import React from 'react';

const PAGE_TITLES = {
    dashboard:    'Dashboard Overview',
    doctors:      'Doctor Management',
    patients:     'Patient Management',
    appointments: 'Appointment Management',
};

const PAGE_ICONS = {
    dashboard:    'fa-gauge',
    doctors:      'fa-user-doctor',
    patients:     'fa-hospital-user',
    appointments: 'fa-calendar-check',
};

export default function Topbar({ activePage }) {
    const now = new Date().toLocaleDateString('en-US', {
        weekday: 'long', year: 'numeric', month: 'long', day: 'numeric'
    });

    return (
        <>
            <div className="page-title">
                <i className={`fa-solid ${PAGE_ICONS[activePage]} me-2 text-primary`}></i>
                {PAGE_TITLES[activePage]}
            </div>
            <div className="topbar-right">
                <span><i className="fa-regular fa-calendar me-1"></i>{now}</span>
                <span
                    style={{
                        background:'#0d6efd', color:'#fff', width:32, height:32,
                        borderRadius:'50%', display:'flex', alignItems:'center',
                        justifyContent:'center', fontWeight:700, fontSize:'0.82rem'
                    }}
                >
                    AD
                </span>
            </div>
        </>
    );
}
