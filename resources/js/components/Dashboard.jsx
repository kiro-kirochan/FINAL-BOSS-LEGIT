import React from 'react';

// Dashboard — shows summary statistics and quick-access cards
export default function Dashboard({ stats, setActivePage }) {
    const statCards = [
        {
            label: 'Total Doctors',
            value: stats?.total_doctors ?? '—',
            icon: 'fa-user-doctor',
            color: '#dbeafe', iconColor: '#1d4ed8',
            page: 'doctors',
        },
        {
            label: 'Total Patients',
            value: stats?.total_patients ?? '—',
            icon: 'fa-hospital-user',
            color: '#d1fae5', iconColor: '#065f46',
            page: 'patients',
        },
        {
            label: 'Total Appointments',
            value: stats?.total_appointments ?? '—',
            icon: 'fa-calendar-check',
            color: '#fae8ff', iconColor: '#7e22ce',
            page: 'appointments',
        },
        {
            label: 'Pending Appointments',
            value: stats?.pending ?? '—',
            icon: 'fa-clock',
            color: '#fef9c3', iconColor: '#854d0e',
            page: 'appointments',
        },
    ];

    const apptBreakdown = [
        { label: 'Confirmed', value: stats?.confirmed ?? 0, cls: 'status-Confirmed' },
        { label: 'Completed', value: stats?.completed ?? 0, cls: 'status-Completed' },
        { label: 'Cancelled', value: stats?.cancelled ?? 0, cls: 'status-Cancelled' },
        { label: 'Pending',   value: stats?.pending   ?? 0, cls: 'status-Pending'   },
    ];

    return (
        <div>
            {/* Welcome banner */}
            <div style={{
                background: 'linear-gradient(135deg,#1d4ed8,#0d6efd)',
                borderRadius: 14,
                padding: '28px 32px',
                color: '#fff',
                marginBottom: 28,
                display: 'flex',
                alignItems: 'center',
                justifyContent: 'space-between',
                flexWrap: 'wrap',
                gap: 16,
            }}>
                <div>
                    <h4 style={{ fontWeight:700, margin:0 }}>
                        <i className="fa-solid fa-hospital me-2"></i>Global Hospital
                    </h4>
                    <p style={{ margin:'6px 0 0', opacity:0.8, fontSize:'0.9rem' }}>
                        Welcome to the Hospital Management System Admin Panel
                    </p>
                </div>
                <div style={{ fontSize:'0.8rem', opacity:0.75 }}>
                    Inspired by kishan0725/Hospital-Management-System<br />
                    Rebuilt with React 18 + Laravel 11 + Bootstrap 5
                </div>
            </div>

            {/* Stat Cards */}
            <div className="row g-3 mb-4">
                {statCards.map(card => (
                    <div className="col-sm-6 col-xl-3" key={card.label}>
                        <div
                            className="stat-card"
                            style={{ cursor:'pointer' }}
                            onClick={() => setActivePage(card.page)}
                        >
                            <div className="stat-icon" style={{ background: card.color }}>
                                <i className={`fa-solid ${card.icon}`} style={{ color: card.iconColor }}></i>
                            </div>
                            <div>
                                <div className="stat-value">{card.value}</div>
                                <div className="stat-label">{card.label}</div>
                            </div>
                        </div>
                    </div>
                ))}
            </div>

            {/* Bottom row: breakdown + quick nav */}
            <div className="row g-3">
                {/* Appointment breakdown */}
                <div className="col-lg-6">
                    <div className="card">
                        <div className="card-header">
                            <div>
                                <div className="card-title">
                                    <i className="fa-solid fa-chart-pie me-2 text-primary"></i>
                                    Appointment Breakdown
                                </div>
                            </div>
                        </div>
                        <div className="card-body">
                            {apptBreakdown.map(item => (
                                <div
                                    key={item.label}
                                    style={{
                                        display:'flex', alignItems:'center',
                                        justifyContent:'space-between',
                                        padding:'10px 0',
                                        borderBottom:'1px solid #f0f4f8'
                                    }}
                                >
                                    <span className={`status-badge ${item.cls}`}>{item.label}</span>
                                    <strong style={{ fontSize:'1.1rem' }}>{item.value}</strong>
                                </div>
                            ))}
                        </div>
                    </div>
                </div>

                {/* Quick access */}
                <div className="col-lg-6">
                    <div className="card">
                        <div className="card-header">
                            <div className="card-title">
                                <i className="fa-solid fa-bolt me-2 text-primary"></i>
                                Quick Access
                            </div>
                        </div>
                        <div className="card-body d-flex flex-column gap-3">
                            {[
                                { page:'doctors',      icon:'fa-user-doctor',    label:'Manage Doctors',      desc:'Add, edit or remove doctors' },
                                { page:'patients',     icon:'fa-hospital-user',  label:'Manage Patients',     desc:'Register and manage patients' },
                                { page:'appointments', icon:'fa-calendar-check', label:'Manage Appointments', desc:'Book and track appointments' },
                            ].map(item => (
                                <button
                                    key={item.page}
                                    className="btn btn-outline-primary text-start d-flex align-items-center gap-3"
                                    onClick={() => setActivePage(item.page)}
                                    style={{ padding:'12px 16px' }}
                                >
                                    <i className={`fa-solid ${item.icon} fs-5`}></i>
                                    <div>
                                        <div style={{ fontWeight:600 }}>{item.label}</div>
                                        <div style={{ fontSize:'0.78rem', opacity:0.7 }}>{item.desc}</div>
                                    </div>
                                </button>
                            ))}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    );
}
