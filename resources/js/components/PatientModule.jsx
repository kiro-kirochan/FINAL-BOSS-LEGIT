import React, { useState, useEffect } from 'react';
import DeleteModal from './DeleteModal';

// ─── PatientModule ─────────────────────────────────────────────────────────────
// Full CRUD for the patreg (patient registration) table
export default function PatientModule({ triggerAlert, onSaveSuccess }) {
    const [view, setView]                   = useState('list');
    const [patients, setPatients]           = useState([]);
    const [selected, setSelected]           = useState(null);
    const [loading, setLoading]             = useState(false);
    const [searchTerm, setSearchTerm]       = useState('');
    const [filterGender, setFilterGender]   = useState('');
    const [showDelete, setShowDelete]       = useState(false);
    const [toDelete, setToDelete]           = useState(null);
    const [backendErrors, setBackendErrors] = useState({});

    const emptyForm = { fname:'', lname:'', email:'', password:'', contact:'', gender:'' };
    const [form, setForm]     = useState(emptyForm);
    const [errors, setErrors] = useState({});

    useEffect(() => { fetchPatients(); }, [searchTerm, filterGender]);

    const fetchPatients = async () => {
        setLoading(true);
        try {
            const params = new URLSearchParams();
            if (searchTerm)    params.append('search', searchTerm);
            if (filterGender)  params.append('gender', filterGender);
            const res  = await fetch(`/api/patients?${params}`);
            const data = await res.json();
            if (data.success) setPatients(data.data);
        } catch { triggerAlert('danger', 'Failed to load patients.'); }
        finally { setLoading(false); }
    };

    const openCreate = () => {
        setSelected(null); setForm(emptyForm);
        setErrors({}); setBackendErrors({}); setView('form');
    };
    const openEdit = (p) => {
        setSelected(p);
        setForm({ fname:p.fname, lname:p.lname, email:p.email, password:'', contact:p.contact, gender:p.gender });
        setErrors({}); setBackendErrors({}); setView('form');
    };
    const handleChange = e => {
        const { name, value } = e.target;
        setForm(prev => ({ ...prev, [name]: value }));
        if (errors[name]) setErrors(prev => ({ ...prev, [name]: '' }));
    };

    const validate = () => {
        const e = {};
        if (!form.fname.trim())   e.fname = 'First name is required.';
        if (!form.lname.trim())   e.lname = 'Last name is required.';
        if (!form.email.trim())   e.email = 'Email is required.';
        else if (!/\S+@\S+\.\S+/.test(form.email)) e.email = 'Invalid email format.';
        if (!selected && !form.password) e.password = 'Password is required.';
        else if (form.password && form.password.length < 6) e.password = 'Password must be at least 6 characters.';
        if (!form.contact.trim()) e.contact = 'Contact number is required.';
        if (!form.gender)         e.gender  = 'Gender is required.';
        setErrors(e);
        return Object.keys(e).length === 0;
    };

    const handleSubmit = async e => {
        e.preventDefault();
        if (!validate()) return;
        setBackendErrors({});

        const url    = selected ? `/api/patients/${selected.id}` : '/api/patients';
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
                fetchPatients(); onSaveSuccess(); setView('list');
            } else {
                triggerAlert('danger', data.message || 'Save failed.');
            }
        } catch { triggerAlert('danger', 'Network error. Could not save patient.'); }
    };

    const confirmDelete = async () => {
        try {
            const res  = await fetch(`/api/patients/${toDelete.id}`, {
                method:'DELETE', headers:{'Accept':'application/json'}
            });
            const data = await res.json();
            if (data.success) { triggerAlert('success', data.message); fetchPatients(); onSaveSuccess(); }
            else triggerAlert('danger', data.message || 'Delete failed.');
        } catch { triggerAlert('danger', 'Network error.'); }
        finally { setShowDelete(false); setToDelete(null); }
    };

    const allErrors = { ...errors, ...backendErrors };
    const genderColor = { Male:'#dbeafe', Female:'#fce7f3', Other:'#fef9c3' };
    const genderTextColor = { Male:'#1d4ed8', Female:'#be185d', Other:'#854d0e' };

    if (view === 'list') return (
        <>
            <div className="card">
                <div className="card-header">
                    <div>
                        <div className="card-title">
                            <i className="fa-solid fa-hospital-user text-primary me-2"></i>
                            Patient Registry
                        </div>
                        <div className="card-subtitle">Register, manage, and search patient records</div>
                    </div>
                    <button className="btn btn-primary btn-sm px-3" onClick={openCreate}>
                        <i className="fa-solid fa-plus me-1"></i> Add New Patient
                    </button>
                </div>

                <div className="filter-bar">
                    <div className="row g-2">
                        <div className="col-md-7">
                            <div className="input-group">
                                <span className="input-group-text"><i className="fa-solid fa-magnifying-glass text-muted"></i></span>
                                <input type="text" className="form-control"
                                    placeholder="Search by name, email, or contact..."
                                    value={searchTerm} onChange={e => setSearchTerm(e.target.value)} />
                                {searchTerm && (
                                    <button className="btn btn-outline-secondary" onClick={() => setSearchTerm('')}>
                                        <i className="fa-solid fa-xmark"></i>
                                    </button>
                                )}
                            </div>
                        </div>
                        <div className="col-md-5">
                            <div className="input-group">
                                <span className="input-group-text"><i className="fa-solid fa-venus-mars text-muted"></i></span>
                                <select className="form-select" value={filterGender} onChange={e => setFilterGender(e.target.value)}>
                                    <option value="">All Genders</option>
                                    <option value="Male">Male</option>
                                    <option value="Female">Female</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <div className="table-responsive">
                    <table className="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th className="ps-4">#</th>
                                <th>Patient</th>
                                <th>Contact</th>
                                <th>Gender</th>
                                <th>Registered On</th>
                                <th className="text-end pe-4">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            {loading ? (
                                <tr><td colSpan="6" className="text-center py-5">
                                    <div className="spinner-border text-primary"></div>
                                    <p className="text-muted small mt-2 mb-0">Loading patients...</p>
                                </td></tr>
                            ) : patients.length > 0 ? patients.map((p, i) => (
                                <tr key={p.id}>
                                    <td className="ps-4 text-muted">{i + 1}</td>
                                    <td>
                                        <div className="d-flex align-items-center gap-2">
                                            <div className="avatar"
                                                style={{ background: genderColor[p.gender] || '#f1f5f9', color: genderTextColor[p.gender] || '#64748b' }}>
                                                {p.fname.charAt(0)}{p.lname.charAt(0)}
                                            </div>
                                            <div>
                                                <div className="fw-semibold">{p.fname} {p.lname}</div>
                                                <div className="text-muted" style={{ fontSize:'0.78rem' }}>{p.email}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>{p.contact}</td>
                                    <td>
                                        <span className="badge rounded-pill px-3 py-1"
                                            style={{ background: genderColor[p.gender] || '#f1f5f9', color: genderTextColor[p.gender] || '#64748b' }}>
                                            {p.gender}
                                        </span>
                                    </td>
                                    <td className="text-muted">
                                        {new Date(p.created_at).toLocaleDateString('en-US',{year:'numeric',month:'short',day:'numeric'})}
                                    </td>
                                    <td className="text-end pe-4">
                                        <div className="d-inline-flex gap-1">
                                            <button className="btn btn-sm btn-outline-primary" onClick={() => openEdit(p)}>
                                                <i className="fa-solid fa-pencil"></i>
                                            </button>
                                            <button className="btn btn-sm btn-outline-danger"
                                                onClick={() => { setToDelete(p); setShowDelete(true); }}>
                                                <i className="fa-solid fa-trash-can"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            )) : (
                                <tr><td colSpan="6" className="text-center py-5">
                                    <i className="fa-solid fa-user-slash fa-2x text-muted mb-2 d-block"></i>
                                    <span className="text-muted">No patients found.</span>
                                </td></tr>
                            )}
                        </tbody>
                    </table>
                </div>

                {!loading && patients.length > 0 && (
                    <div className="card-footer-note">
                        Showing <strong>{patients.length}</strong> patient{patients.length !== 1 ? 's' : ''}
                    </div>
                )}
            </div>

            <DeleteModal
                show={showDelete}
                recordName={toDelete ? `${toDelete.fname} ${toDelete.lname}` : ''}
                recordType="patient"
                onConfirm={confirmDelete}
                onCancel={() => { setShowDelete(false); setToDelete(null); }}
            />
        </>
    );

    // ── FORM VIEW ──
    return (
        <div className="card">
            <div className="card-header">
                <div>
                    <div className="card-title" style={{ color:'#0d6efd' }}>
                        <i className={`fa-solid ${selected ? 'fa-user-pen' : 'fa-user-plus'} me-2`}></i>
                        {selected ? 'Edit Patient Record' : 'Register New Patient'}
                    </div>
                    <div className="card-subtitle">
                        {selected ? 'Update patient information.' : 'Fill all required fields to register a patient.'}
                    </div>
                </div>
                <button className="btn btn-sm btn-light" onClick={() => setView('list')}>
                    <i className="fa-solid fa-arrow-left me-1"></i> Back
                </button>
            </div>
            <div className="card-body p-4">
                <form onSubmit={handleSubmit} noValidate>
                    <div className="row g-3">

                        <div className="col-md-6">
                            <label className="form-label">First Name <span className="text-danger">*</span></label>
                            <div className="input-group has-validation">
                                <span className="input-group-text"><i className="fa-solid fa-user text-muted"></i></span>
                                <input type="text" name="fname" className={`form-control ${allErrors.fname?'is-invalid':''}`}
                                    placeholder="First name" value={form.fname} onChange={handleChange} />
                                {allErrors.fname && <div className="invalid-feedback">{allErrors.fname}</div>}
                            </div>
                        </div>

                        <div className="col-md-6">
                            <label className="form-label">Last Name <span className="text-danger">*</span></label>
                            <div className="input-group has-validation">
                                <span className="input-group-text"><i className="fa-solid fa-user text-muted"></i></span>
                                <input type="text" name="lname" className={`form-control ${allErrors.lname?'is-invalid':''}`}
                                    placeholder="Last name" value={form.lname} onChange={handleChange} />
                                {allErrors.lname && <div className="invalid-feedback">{allErrors.lname}</div>}
                            </div>
                        </div>

                        <div className="col-md-6">
                            <label className="form-label">Email Address <span className="text-danger">*</span></label>
                            <div className="input-group has-validation">
                                <span className="input-group-text"><i className="fa-solid fa-envelope text-muted"></i></span>
                                <input type="email" name="email" className={`form-control ${allErrors.email?'is-invalid':''}`}
                                    placeholder="patient@email.com" value={form.email} onChange={handleChange} />
                                {allErrors.email && <div className="invalid-feedback">{allErrors.email}</div>}
                            </div>
                        </div>

                        <div className="col-md-6">
                            <label className="form-label">
                                Password {selected && <small className="text-muted fw-normal">(leave blank to keep current)</small>}
                                {!selected && <span className="text-danger"> *</span>}
                            </label>
                            <div className="input-group has-validation">
                                <span className="input-group-text"><i className="fa-solid fa-key text-muted"></i></span>
                                <input type="password" name="password" className={`form-control ${allErrors.password?'is-invalid':''}`}
                                    placeholder={selected ? '••••••••' : 'Min. 6 characters'} value={form.password} onChange={handleChange} />
                                {allErrors.password && <div className="invalid-feedback">{allErrors.password}</div>}
                            </div>
                        </div>

                        <div className="col-md-6">
                            <label className="form-label">Contact Number <span className="text-danger">*</span></label>
                            <div className="input-group has-validation">
                                <span className="input-group-text"><i className="fa-solid fa-phone text-muted"></i></span>
                                <input type="text" name="contact" className={`form-control ${allErrors.contact?'is-invalid':''}`}
                                    placeholder="e.g. 09171234567" value={form.contact} onChange={handleChange} />
                                {allErrors.contact && <div className="invalid-feedback">{allErrors.contact}</div>}
                            </div>
                        </div>

                        <div className="col-md-6">
                            <label className="form-label">Gender <span className="text-danger">*</span></label>
                            <div className="input-group has-validation">
                                <span className="input-group-text"><i className="fa-solid fa-venus-mars text-muted"></i></span>
                                <select name="gender" className={`form-select ${allErrors.gender?'is-invalid':''}`}
                                    value={form.gender} onChange={handleChange}>
                                    <option value="">-- Select Gender --</option>
                                    <option value="Male">Male</option>
                                    <option value="Female">Female</option>
                                    <option value="Other">Other</option>
                                </select>
                                {allErrors.gender && <div className="invalid-feedback">{allErrors.gender}</div>}
                            </div>
                        </div>

                    </div>

                    <div className="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                        <button type="button" className="btn btn-light" onClick={() => setView('list')}>Cancel</button>
                        <button type="submit" className="btn btn-primary px-4">
                            <i className="fa-solid fa-floppy-disk me-1"></i>
                            {selected ? 'Update Patient' : 'Register Patient'}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    );
}
