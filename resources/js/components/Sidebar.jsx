import React from 'react';

// Sidebar — left navigation panel. Receives activePage & setActivePage from App.jsx
export default function Sidebar({ activePage, setActivePage }) {
    const navItems = [
        { key: 'dashboard',    icon: 'fa-gauge',          label: 'Dashboard' },
        { key: 'doctors',      icon: 'fa-user-doctor',    label: 'Doctors' },
        { key: 'patients',     icon: 'fa-hospital-user',  label: 'Patients' },
        { key: 'appointments', icon: 'fa-calendar-check', label: 'Appointments' },
    ];

    return (
        <>
            {/* Brand / Logo area */}
            <div className="sidebar-brand">
                <div style={{ display:'flex', alignItems:'center', gap:10 }}>
                    <div style={{
                        width:36, height:36, borderRadius:10,
                        background:'#0d6efd', display:'flex',
                        alignItems:'center', justifyContent:'center', flexShrink:0
                    }}>
                        <i className="fa-solid fa-hospital" style={{ color:'#fff', fontSize:'1rem' }}></i>
                    </div>
                    <div>
                        <h5>Global Hospital</h5>
                        <p>Management System</p>
                    </div>
                </div>
            </div>

            {/* Navigation links */}
            <div className="sidebar-nav">
                <div className="nav-label">Main Menu</div>
                {navItems.map(item => (
                    <a
                        key={item.key}
                        href="#"
                        className={activePage === item.key ? 'active' : ''}
                        onClick={e => { e.preventDefault(); setActivePage(item.key); }}
                    >
                        <i className={`fa-solid ${item.icon}`}></i>
                        {item.label}
                    </a>
                ))}
            </div>

            {/* Footer */}
            <div style={{ padding:'16px 20px', borderTop:'1px solid rgba(255,255,255,0.08)' }}>
                <div style={{ fontSize:'0.72rem', color:'rgba(255,255,255,0.3)' }}>
                    Web Systems &amp; Technologies<br />Final Project — A.Y. 2025-2026
                </div>
            </div>
        </>
    );
}
