import React, { useState, useEffect } from 'react';
import DeleteModal from './DeleteModal';

const SPECIALIZATIONS = [
    'Cardiology','Pediatrics','Dermatology','Neurology',
    'General Medicine','Orthopedics','Oncology','Psychiatry','Gynecology','ENT'
];

// ─── DoctorModule ─────────────────────────────────────────────────────────────
// Manages the full Doctor CRUD: list view ↔ form view
export default function DoctorModule({ triggerAlert, onSaveSuccess }) {
    const [view, setView]                   = useState('list');   // 'list' | 'form'
    const [doctors, setDoctors]             = useState([]);
    const [selected, setSelected]           = useState(null);     // doctor being edited
    const [loading, setLoading]             = useState(false);
    const [searchTerm, setSearchTerm]       = useState('');
    const [filterSpec, setFilterSpec]       = useState('');
    const [showDelete, setShowDelete]       = useState(false);
    const [toDelete, setToDelete]           = useState(null);
    const [backendErrors, setBackendErrors] = useState({});

    // Form state
    const emptyForm = { name:'', email:'', password:'', specialization:'', docFees:'' };
    const [form, setForm] = useState(emptyForm);
    const [errors, setErrors] = useState({});

    // Fetch doctors whenever search/filter changes
    useEffect(() => { fetchDoctors(); }, [searchTerm, filterSpec]);

    const fetchDoctors = async () => {
        setLoading(true);
        try {
            const params = new URLSearchParams();
            if (searchTerm) params.append('search', searchTerm);
            if (filterSpec) params.append('specialization', filterSpec);
            const res  = await fetch(`/api/doctors?${params}`);
            const data = await res.json();
            if (data.success) setDoctors(data.data);
        } catch { triggerAlert('danger', 'Failed to load doctors.'); }
        finally { setLoading(false); }
    };

    // Open "Add New" form
    const openCreate = () => {
        setSelected(null);
        setForm(emptyForm);
        setErrors({}); setBackendErrors({});
        setView('form');
    };

    // Open "Edit" form pre-filled with doctor data
    const openEdit = (doc) => {
        setSelected(doc);
        setForm({ name:doc.name, email:doc.email, password:'', specialization:doc.specialization, docFees:doc.docFees });
        setErrors({}); setBackendErrors({});
        setView('form');
    };

    // Field change handler — also clears that field's error
    const handleChange = e => {
        const { name, value } = e.target;
        setForm(prev => ({ ...prev, [name]: value }));
        if (errors[name]) setErrors(prev => ({ ...prev, [name]: '' }));
    };

    // Client-side validation
    const validate = () => {
        const e = {};
        if (!form.name.trim())        e.name = 'Doctor name is required.';
        if (!form.email.trim())       e.email = 'Email is required.';
        else if (!/\S+@\S+\.\S+/.test(form.email)) e.email = 'Email format is invalid.';
        if (!selected && !form.password) e.password = 'Password is required.';
        else if (form.password && form.password.length < 6) e.password = 'Password must be at least 6 characters.';
        if (!form.specialization)     e.specialization = 'Please select a specialization.';
        if (form.docFees === '')      e.docFees = 'Consultation fee is required.';
        else if (isNaN(form.docFees) || Number(form.docFees) < 0) e.docFees = 'Fee must be a positive number.';
        setErrors(e);
        return Object.keys(e).length === 0;
    };

    // Submit — POST (create) or PUT (update)
    const handleSubmit = async e => {
        e.preventDefault();
        if (!validate()) return;
        setBackendErrors({});

        const url    = selected ? `/api/doctors/${selected.id}` : '/api/doctors';
        const method = selected ? 'PUT' : 'POST';

        try {
            const res  = await fetch(url, {
                method,
                headers: { 'Content-Type':'application/json', 'Accept':'application/json' },
                body: JSON.stringify(form),
            });
            const data = await res.json();

            if (res.status === 422) {
                const fe = {};
                Object.keys(data.errors || {}).forEach(k => fe[k] = data.errors[k][0]);
                setBackendErrors(fe);
                triggerAlert('danger', 'Validation errors. Please review the form.');
            } else if (data.success) {
                triggerAlert('success', data.message);
                fetchDoctors();
                onSaveSuccess();
                setView('list');
            } else {
                triggerAlert('danger', data.message || 'Save failed.');
            }
        } catch { triggerAlert('danger', 'Network error. Could not save doctor.'); }
    };

    // Delete
    const confirmDelete = async () => {
        try {
            const res  = await fetch(`/api/doctors/${toDelete.id}`, {
                method:'DELETE', headers:{'Accept':'application/json'}
            });
            const data = await res.json();
            if (data.success) {
                triggerAlert('success', data.message);
                fetchDoctors(); onSaveSuccess();
            } else { triggerAlert('danger', data.message || 'Delete failed.'); }
        } catch { triggerAlert('danger', 'Network error. Could not delete.'); }
        finally { setShowDelete(false); setToDelete(null); }
    };

    const allErrors = { ...errors, ...backendErrors };

    // ── LIST VIEW ──────────────────────────────────────────────────────────────
    if (view === 'list') return (
        <>
            <div className="card">
                {/* Header */}
                <div className="card-header">
                    <div>
                        <div className="card-title">
                            <i className="fa-solid fa-user-doctor text-primary me-2"></i>
                            Medical Staff Directory
                        </div>
                        <div className="card-subtitle">Create, read, update and delete doctor profiles</div>
                    </div>
                    <button className="btn btn-primary btn-sm px-3" onClick={openCreate}>
                        <i className="fa-solid fa-plus me-1"></i> Add New Doctor
                    </button>
                </div>

                {/* Filter bar */}
                <div className="filter-bar">
                    <div className="row g-2">
                        <div className="col-md-7">
                            <div className="input-group">
                                <span className="input-group-text"><i className="fa-solid fa-magnifying-glass text-muted"></i></span>
                                <input
                                    type="text" className="form-control"
                                    placeholder="Search by name or email..."
                                    value={searchTerm}
                                    onChange={e => setSearchTerm(e.target.value)}
                                />
                                {searchTerm && (
                                    <button className="btn btn-outline-secondary" onClick={() => setSearchTerm('')}>
                                        <i className="fa-solid fa-xmark"></i>
                                    </button>
                                )}
                            </div>
                        </div>
                        <div className="col-md-5">
                            <div className="input-group">
                                <span className="input-group-text"><i className="fa-solid fa-filter text-muted"></i></span>
                                <select className="form-select" value={filterSpec} onChange={e => setFilterSpec(e.target.value)}>
                                    <option value="">All Specializations</option>
                                    {SPECIALIZATIONS.map(s => <option key={s} value={s}>{s}</option>)}
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                {/* Table */}
                <div className="table-responsive">
                    <table className="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th className="ps-4">#</th>
                                <th>Doctor</th>
                                <th>Specialization</th>
                                <th>Consultation Fee</th>
                                <th>Date Added</th>
                                <th className="text-end pe-4">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            {loading ? (
                                <tr><td colSpan="6" className="text-center py-5">
                                    <div className="spinner-border text-primary" role="status"></div>
                                    <p className="text-muted small mt-2 mb-0">Loading doctors...</p>
                                </td></tr>
                            ) : doctors.length > 0 ? doctors.map((doc, i) => (
                                <tr key={doc.id}>
                                    <td className="ps-4 text-muted">{i + 1}</td>
                                    <td>
                                        <div className="d-flex align-items-center gap-2">
                                            <div className="avatar" style={{ background:'#dbeafe', color:'#1d4ed8' }}>
                                                {doc.name.replace('Dr. ','').substring(0,2).toUpperCase()}
                                            </div>
                                            <div>
                                                <div className="fw-semibold">{doc.name}</div>
                                                <div className="text-muted" style={{ fontSize:'0.78rem' }}>{doc.email}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span className="badge rounded-pill bg-primary-subtle text-primary border border-primary-subtle px-3 py-1">
                                            {doc.specialization}
                                        </span>
                                    </td>
                                    <td className="fw-semibold">${parseFloat(doc.docFees).toFixed(2)}</td>
                                    <td className="text-muted">
                                        {new Date(doc.created_at).toLocaleDateString('en-US',{year:'numeric',month:'short',day:'numeric'})}
                                    </td>
                                    <td className="text-end pe-4">
                                        <div className="d-inline-flex gap-1">
                                            <button className="btn btn-sm btn-outline-primary" title="Edit" onClick={() => openEdit(doc)}>
                                                <i className="fa-solid fa-pencil"></i>
                                            </button>
                                            <button className="btn btn-sm btn-outline-danger" title="Delete"
                                                onClick={() => { setToDelete(doc); setShowDelete(true); }}>
                                                <i className="fa-solid fa-trash-can"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            )) : (
                                <tr><td colSpan="6" className="text-center py-5">
                                    <i className="fa-solid fa-user-slash fa-2x text-muted mb-2 d-block"></i>
                                    <span className="text-muted">No doctors found. Try adjusting your search.</span>
                                </td></tr>
                            )}
                        </tbody>
                    </table>
                </div>

                {!loading && doctors.length > 0 && (
                    <div className="card-footer-note">
                        Showing <strong>{doctors.length}</strong> doctor{doctors.length !== 1 ? 's' : ''}
                    </div>
                )}
            </div>

            <DeleteModal
                show={showDelete}
                recordName={toDelete?.name}
                recordType="doctor"
                onConfirm={confirmDelete}
                onCancel={() => { setShowDelete(false); setToDelete(null); }}
            />
        </>
    );

    // ── FORM VIEW (Create / Edit) ──────────────────────────────────────────────
    return (
        <div className="card">
            <div className="card-header">
                <div>
                    <div className="card-title" style={{ color:'#0d6efd' }}>
                        <i className={`fa-solid ${selected ? 'fa-user-pen' : 'fa-user-plus'} me-2`}></i>
                        {selected ? 'Edit Doctor Profile' : 'Register New Doctor'}
                    </div>
                    <div className="card-subtitle">
                        {selected ? 'Update the doctor\'s information below.' : 'Fill in all fields to add a new doctor.'}
                    </div>
                </div>
                <button className="btn btn-sm btn-light" onClick={() => setView('list')}>
                    <i className="fa-solid fa-arrow-left me-1"></i> Back
                </button>
            </div>

            <div className="card-body p-4">
                <form onSubmit={handleSubmit} noValidate>
                    <div className="row g-3">

                        {/* Name */}
                        <div className="col-md-6">
                            <label className="form-label">Full Name <span className="text-danger">*</span></label>
                            <div className="input-group has-validation">
                                <span className="input-group-text"><i className="fa-solid fa-user-md text-muted"></i></span>
                                <input type="text" name="name" className={`form-control ${allErrors.name ? 'is-invalid' : ''}`}
                                    placeholder="e.g. Dr. Jane Doe" value={form.name} onChange={handleChange} />
                                {allErrors.name && <div className="invalid-feedback">{allErrors.name}</div>}
                            </div>
                        </div>

                        {/* Email */}
                        <div className="col-md-6">
                            <label className="form-label">Email Address <span className="text-danger">*</span></label>
                            <div className="input-group has-validation">
                                <span className="input-group-text"><i className="fa-solid fa-envelope text-muted"></i></span>
                                <input type="email" name="email" className={`form-control ${allErrors.email ? 'is-invalid' : ''}`}
                                    placeholder="doctor@hospital.com" value={form.email} onChange={handleChange} />
                                {allErrors.email && <div className="invalid-feedback">{allErrors.email}</div>}
                            </div>
                        </div>

                        {/* Password */}
                        <div className="col-md-6">
                            <label className="form-label">
                                Password {selected && <small className="text-muted fw-normal">(leave blank to keep current)</small>}
                                {!selected && <span className="text-danger"> *</span>}
                            </label>
                            <div className="input-group has-validation">
                                <span className="input-group-text"><i className="fa-solid fa-key text-muted"></i></span>
                                <input type="password" name="password" className={`form-control ${allErrors.password ? 'is-invalid' : ''}`}
                                    placeholder={selected ? '••••••••' : 'Minimum 6 characters'} value={form.password} onChange={handleChange} />
                                {allErrors.password && <div className="invalid-feedback">{allErrors.password}</div>}
                            </div>
                        </div>

                        {/* Specialization */}
                        <div className="col-md-6">
                            <label className="form-label">Specialization <span className="text-danger">*</span></label>
                            <div className="input-group has-validation">
                                <span className="input-group-text"><i className="fa-solid fa-stethoscope text-muted"></i></span>
                                <select name="specialization" className={`form-select ${allErrors.specialization ? 'is-invalid' : ''}`}
                                    value={form.specialization} onChange={handleChange}>
                                    <option value="">-- Select Specialty --</option>
                                    {SPECIALIZATIONS.map(s => <option key={s} value={s}>{s}</option>)}
                                    {form.specialization && !SPECIALIZATIONS.includes(form.specialization) && (
                                        <option value={form.specialization}>{form.specialization}</option>
                                    )}
                                </select>
                                {allErrors.specialization && <div className="invalid-feedback">{allErrors.specialization}</div>}
                            </div>
                        </div>

                        {/* Fees */}
                        <div className="col-md-6">
                            <label className="form-label">Consultation Fee ($) <span className="text-danger">*</span></label>
                            <div className="input-group has-validation">
                                <span className="input-group-text"><i className="fa-solid fa-dollar-sign text-muted"></i></span>
                                <input type="number" step="0.01" name="docFees" className={`form-control ${allErrors.docFees ? 'is-invalid' : ''}`}
                                    placeholder="0.00" value={form.docFees} onChange={handleChange} />
                                {allErrors.docFees && <div className="invalid-feedback">{allErrors.docFees}</div>}
                            </div>
                        </div>

                    </div>

                    <div className="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                        <button type="button" className="btn btn-light" onClick={() => setView('list')}>Cancel</button>
                        <button type="submit" className="btn btn-primary px-4">
                            <i className="fa-solid fa-floppy-disk me-1"></i>
                            {selected ? 'Update Doctor' : 'Register Doctor'}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    );
}
