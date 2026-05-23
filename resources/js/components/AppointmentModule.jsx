import React, { useState, useEffect } from 'react';
import DeleteModal from './DeleteModal';

const STATUSES = ['Pending', 'Confirmed', 'Completed', 'Cancelled'];

// ─── AppointmentModule ──────────────────────────────────────────────────────
// Full CRUD for the appointmenttb table.
// Loads patients and doctors via API for dropdown selectors.
export default function AppointmentModule({ triggerAlert, onSaveSuccess }) {
    const [view, setView]                   = useState('list');
    const [appointments, setAppointments]   = useState([]);
    const [doctors, setDoctors]             = useState([]);
    const [patients, setPatients]           = useState([]);
    const [selected, setSelected]           = useState(null);
    const [loading, setLoading]             = useState(false);
    const [searchTerm, setSearchTerm]       = useState('');
    const [filterStatus, setFilterStatus]   = useState('');
    const [showDelete, setShowDelete]       = useState(false);
    const [toDelete, setToDelete]           = useState(null);
    const [backendErrors, setBackendErrors] = useState({});

    const emptyForm = { patient_id:'', doctor_id:'', appdate:'', apptime:'', status:'Pending' };
    const [form, setForm]     = useState(emptyForm);
    const [errors, setErrors] = useState({});

    // Load appointments on filter/search change
    useEffect(() => { fetchAppointments(); }, [searchTerm, filterStatus]);

    // Load doctors & patients once for dropdowns
    useEffect(() => {
        fetchDropdowns();
    }, []);

    const fetchAppointments = async () => {
        setLoading(true);
        try {
            const params = new URLSearchParams();
            if (searchTerm)   params.append('search', searchTerm);
            if (filterStatus) params.append('status', filterStatus);
            const res  = await fetch(`/api/appointments?${params}`);
            const data = await res.json();
            if (data.success) setAppointments(data.data);
        } catch { triggerAlert('danger', 'Failed to load appointments.'); }
        finally { setLoading(false); }
    };

    const fetchDropdowns = async () => {
        try {
            const [dRes, pRes] = await Promise.all([
                fetch('/api/doctors'),
                fetch('/api/patients'),
            ]);
            const dData = await dRes.json();
            const pData = await pRes.json();
            if (dData.success) setDoctors(dData.data);
            if (pData.success) setPatients(pData.data);
        } catch { console.error('Dropdown load error'); }
    };

    const openCreate = () => {
        setSelected(null); setForm(emptyForm);
        setErrors({}); setBackendErrors({}); setView('form');
    };

    const openEdit = (appt) => {
        setSelected(appt);
        setForm({
            patient_id: String(appt.patient_id),
            doctor_id:  String(appt.doctor_id),
            appdate:    appt.appdate ? appt.appdate.substring(0,10) : '',
            apptime:    appt.apptime ? appt.apptime.substring(0,5) : '',
            status:     appt.status,
        });
        setErrors({}); setBackendErrors({}); setView('form');
    };

    const handleChange = e => {
        const { name, value } = e.target;
        setForm(prev => ({ ...prev, [name]: value }));
        if (errors[name]) setErrors(prev => ({ ...prev, [name]: '' }));
    };

    const validate = () => {
        const e = {};
        if (!form.patient_id) e.patient_id = 'Please select a patient.';
        if (!form.doctor_id)  e.doctor_id  = 'Please select a doctor.';
        if (!form.appdate)    e.appdate     = 'Appointment date is required.';
        if (!form.apptime)    e.apptime     = 'Appointment time is required.';
        if (!form.status)     e.status      = 'Status is required.';
        setErrors(e);
        return Object.keys(e).length === 0;
    };

    const handleSubmit = async e => {
        e.preventDefault();
        if (!validate()) return;
        setBackendErrors({});

        const url    = selected ? `/api/appointments/${selected.id}` : '/api/appointments';
        const method = selected ? 'PUT' : 'POST';

        try {
            const res  = await fetch(url, {
                method,
                headers: { 'Content-Type':'application/json', 'Accept':'application/json' },
                body: JSON.stringify(form),
            });
            const data = await res.json();

            if (res.status === 422) {
                // Could be Laravel validation OR our custom slot-conflict message
                if (data.errors) {
                    const fe = {};
                    Object.keys(data.errors).forEach(k => fe[k] = data.errors[k][0]);
                    setBackendErrors(fe);
                }
                triggerAlert('danger', data.message || 'Validation errors in the form.');
            } else if (data.success) {
                triggerAlert('success', data.message);
                fetchAppointments(); onSaveSuccess(); setView('list');
            } else {
                triggerAlert('danger', data.message || 'Save failed.');
            }
        } catch { triggerAlert('danger', 'Network error. Could not save appointment.'); }
    };

    const confirmDelete = async () => {
        try {
            const res  = await fetch(`/api/appointments/${toDelete.id}`, {
                method:'DELETE', headers:{'Accept':'application/json'}
            });
            const data = await res.json();
            if (data.success) { triggerAlert('success', data.message); fetchAppointments(); onSaveSuccess(); }
            else triggerAlert('danger', data.message || 'Delete failed.');
        } catch { triggerAlert('danger', 'Network error.'); }
        finally { setShowDelete(false); setToDelete(null); }
    };

    const allErrors = { ...errors, ...backendErrors };

    // Helper: get patient full name from appointment
    const patientName = (appt) => {
        if (appt.patient) return `${appt.patient.fname} ${appt.patient.lname}`;
        const p = patients.find(x => x.id === appt.patient_id);
        return p ? `${p.fname} ${p.lname}` : `Patient #${appt.patient_id}`;
    };

    // ── LIST VIEW ──
    if (view === 'list') return (
        <>
            <div className="card">
                <div className="card-header">
                    <div>
                        <div className="card-title">
                            <i className="fa-solid fa-calendar-check text-primary me-2"></i>
                            Appointment Records
                        </div>
                        <div className="card-subtitle">Book, view, edit and cancel appointments</div>
                    </div>
                    <button className="btn btn-primary btn-sm px-3" onClick={openCreate}>
                        <i className="fa-solid fa-plus me-1"></i> Book Appointment
                    </button>
                </div>

                {/* Filter bar */}
                <div className="filter-bar">
                    <div className="row g-2">
                        <div className="col-md-7">
                            <div className="input-group">
                                <span className="input-group-text"><i className="fa-solid fa-magnifying-glass text-muted"></i></span>
                                <input type="text" className="form-control"
                                    placeholder="Search by patient or doctor name..."
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
                                <span className="input-group-text"><i className="fa-solid fa-filter text-muted"></i></span>
                                <select className="form-select" value={filterStatus} onChange={e => setFilterStatus(e.target.value)}>
                                    <option value="">All Statuses</option>
                                    {STATUSES.map(s => <option key={s} value={s}>{s}</option>)}
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
                                <th>Patient</th>
                                <th>Doctor</th>
                                <th>Date</th>
                                <th>Time</th>
                                <th>Status</th>
                                <th className="text-end pe-4">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            {loading ? (
                                <tr><td colSpan="7" className="text-center py-5">
                                    <div className="spinner-border text-primary"></div>
                                    <p className="text-muted small mt-2 mb-0">Loading appointments...</p>
                                </td></tr>
                            ) : appointments.length > 0 ? appointments.map((appt, i) => (
                                <tr key={appt.id}>
                                    <td className="ps-4 text-muted">{i + 1}</td>
                                    <td>
                                        <div className="fw-semibold">{patientName(appt)}</div>
                                        {appt.patient && (
                                            <div className="text-muted" style={{fontSize:'0.78rem'}}>{appt.patient.contact}</div>
                                        )}
                                    </td>
                                    <td>
                                        <div className="fw-semibold">{appt.doctor?.name || `Doctor #${appt.doctor_id}`}</div>
                                        {appt.doctor && (
                                            <div className="text-muted" style={{fontSize:'0.78rem'}}>{appt.doctor.specialization}</div>
                                        )}
                                    </td>
                                    <td className="fw-semibold">
                                        {new Date(appt.appdate).toLocaleDateString('en-US',{year:'numeric',month:'short',day:'numeric'})}
                                    </td>
                                    <td>
                                        {appt.apptime
                                            ? new Date(`1970-01-01T${appt.apptime}`).toLocaleTimeString('en-US',{hour:'2-digit',minute:'2-digit'})
                                            : appt.apptime}
                                    </td>
                                    <td>
                                        <span className={`status-badge status-${appt.status}`}>{appt.status}</span>
                                    </td>
                                    <td className="text-end pe-4">
                                        <div className="d-inline-flex gap-1">
                                            <button className="btn btn-sm btn-outline-primary" onClick={() => openEdit(appt)}>
                                                <i className="fa-solid fa-pencil"></i>
                                            </button>
                                            <button className="btn btn-sm btn-outline-danger"
                                                onClick={() => { setToDelete(appt); setShowDelete(true); }}>
                                                <i className="fa-solid fa-trash-can"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            )) : (
                                <tr><td colSpan="7" className="text-center py-5">
                                    <i className="fa-solid fa-calendar-xmark fa-2x text-muted mb-2 d-block"></i>
                                    <span className="text-muted">No appointments found.</span>
                                </td></tr>
                            )}
                        </tbody>
                    </table>
                </div>

                {!loading && appointments.length > 0 && (
                    <div className="card-footer-note">
                        Showing <strong>{appointments.length}</strong> appointment{appointments.length !== 1 ? 's' : ''}
                    </div>
                )}
            </div>

            <DeleteModal
                show={showDelete}
                recordName={toDelete ? `Appointment #${toDelete.id}` : ''}
                recordType="appointment"
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
                        <i className={`fa-solid ${selected ? 'fa-calendar-pen' : 'fa-calendar-plus'} me-2`}></i>
                        {selected ? 'Edit Appointment' : 'Book New Appointment'}
                    </div>
                    <div className="card-subtitle">
                        {selected ? 'Modify appointment details below.' : 'Select a patient, doctor, date and time.'}
                    </div>
                </div>
                <button className="btn btn-sm btn-light" onClick={() => setView('list')}>
                    <i className="fa-solid fa-arrow-left me-1"></i> Back
                </button>
            </div>
            <div className="card-body p-4">
                <form onSubmit={handleSubmit} noValidate>
                    <div className="row g-3">

                        {/* Patient select */}
                        <div className="col-md-6">
                            <label className="form-label">Patient <span className="text-danger">*</span></label>
                            <div className="input-group has-validation">
                                <span className="input-group-text"><i className="fa-solid fa-hospital-user text-muted"></i></span>
                                <select name="patient_id" className={`form-select ${allErrors.patient_id?'is-invalid':''}`}
                                    value={form.patient_id} onChange={handleChange}>
                                    <option value="">-- Select Patient --</option>
                                    {patients.map(p => (
                                        <option key={p.id} value={p.id}>{p.fname} {p.lname} ({p.email})</option>
                                    ))}
                                </select>
                                {allErrors.patient_id && <div className="invalid-feedback">{allErrors.patient_id}</div>}
                            </div>
                        </div>

                        {/* Doctor select */}
                        <div className="col-md-6">
                            <label className="form-label">Doctor <span className="text-danger">*</span></label>
                            <div className="input-group has-validation">
                                <span className="input-group-text"><i className="fa-solid fa-user-doctor text-muted"></i></span>
                                <select name="doctor_id" className={`form-select ${allErrors.doctor_id?'is-invalid':''}`}
                                    value={form.doctor_id} onChange={handleChange}>
                                    <option value="">-- Select Doctor --</option>
                                    {doctors.map(d => (
                                        <option key={d.id} value={d.id}>{d.name} — {d.specialization} (${parseFloat(d.docFees).toFixed(2)})</option>
                                    ))}
                                </select>
                                {allErrors.doctor_id && <div className="invalid-feedback">{allErrors.doctor_id}</div>}
                            </div>
                        </div>

                        {/* Date */}
                        <div className="col-md-6">
                            <label className="form-label">Appointment Date <span className="text-danger">*</span></label>
                            <div className="input-group has-validation">
                                <span className="input-group-text"><i className="fa-solid fa-calendar text-muted"></i></span>
                                <input type="date" name="appdate" className={`form-control ${allErrors.appdate?'is-invalid':''}`}
                                    value={form.appdate} onChange={handleChange}
                                    min={new Date().toISOString().split('T')[0]} />
                                {allErrors.appdate && <div className="invalid-feedback">{allErrors.appdate}</div>}
                            </div>
                        </div>

                        {/* Time */}
                        <div className="col-md-6">
                            <label className="form-label">Appointment Time <span className="text-danger">*</span></label>
                            <div className="input-group has-validation">
                                <span className="input-group-text"><i className="fa-solid fa-clock text-muted"></i></span>
                                <input type="time" name="apptime" className={`form-control ${allErrors.apptime?'is-invalid':''}`}
                                    value={form.apptime} onChange={handleChange} />
                                {allErrors.apptime && <div className="invalid-feedback">{allErrors.apptime}</div>}
                            </div>
                        </div>

                        {/* Status */}
                        <div className="col-md-6">
                            <label className="form-label">Status <span className="text-danger">*</span></label>
                            <div className="input-group has-validation">
                                <span className="input-group-text"><i className="fa-solid fa-circle-info text-muted"></i></span>
                                <select name="status" className={`form-select ${allErrors.status?'is-invalid':''}`}
                                    value={form.status} onChange={handleChange}>
                                    {STATUSES.map(s => <option key={s} value={s}>{s}</option>)}
                                </select>
                                {allErrors.status && <div className="invalid-feedback">{allErrors.status}</div>}
                            </div>
                        </div>

                        {/* Info box about time slot conflict */}
                        <div className="col-12">
                            <div className="alert alert-info py-2 px-3 mb-0" style={{fontSize:'0.82rem'}}>
                                <i className="fa-solid fa-circle-info me-2"></i>
                                The system will automatically check for <strong>time slot conflicts</strong>. 
                                If the selected doctor already has a Pending or Confirmed appointment at the same date &amp; time, 
                                the booking will be rejected.
                            </div>
                        </div>

                    </div>

                    <div className="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                        <button type="button" className="btn btn-light" onClick={() => setView('list')}>Cancel</button>
                        <button type="submit" className="btn btn-primary px-4">
                            <i className="fa-solid fa-floppy-disk me-1"></i>
                            {selected ? 'Update Appointment' : 'Book Appointment'}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    );
}
