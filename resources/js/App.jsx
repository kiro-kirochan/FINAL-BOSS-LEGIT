import React, { useState, useEffect, useCallback } from 'react';
import Sidebar from './components/Sidebar';
import Topbar from './components/Topbar';
import Dashboard from './components/Dashboard';
import DoctorModule from './components/DoctorModule';
import PatientModule from './components/PatientModule';
import AppointmentModule from './components/AppointmentModule';

// ─── App.jsx is the ROOT of the entire SPA ───────────────────────────────────
// It holds the current "page" (activePage state) and renders the right module.
// It also manages global alert notifications shared across all modules.
export default function App() {
    // Which page/section is currently visible
    const [activePage, setActivePage] = useState('dashboard');

    // Global alert banner: { type: 'success'|'danger', message: '' }
    const [alert, setAlert] = useState(null);

    // Dashboard stats loaded from Laravel API
    const [stats, setStats] = useState(null);

    // Fetch dashboard stats whenever the dashboard tab is opened
    useEffect(() => {
        if (activePage === 'dashboard') fetchStats();
    }, [activePage]);

    const fetchStats = async () => {
        try {
            const res = await fetch('/api/stats');
            const data = await res.json();
            if (data.success) setStats(data.data);
        } catch (e) {
            console.error('Stats fetch error:', e);
        }
    };

    // triggerAlert — called from any child module to show a global notification
    const triggerAlert = useCallback((type, message) => {
        setAlert({ type, message });
        setTimeout(() => setAlert(null), 5000);
    }, []);

    // onSaveSuccess — called after any Create/Update to refresh stats
    const onSaveSuccess = useCallback(() => {
        fetchStats();
    }, []);

    return (
        <div style={{ display: 'flex' }}>
            {/* ── Left sidebar navigation ── */}
            <div id="sidebar">
                <Sidebar activePage={activePage} setActivePage={setActivePage} />
            </div>

            {/* ── Main content area ── */}
            <div id="main-content">
                <div id="topbar">
                    <Topbar activePage={activePage} />
                </div>

                <div className="page-body">
                    {/* Global alert — appears at top of every page */}
                    {alert && (
                        <div className={`alert alert-${alert.type} d-flex align-items-center justify-content-between mb-4 shadow-sm`}>
                            <span>
                                <i className={`fa-solid me-2 ${alert.type === 'success' ? 'fa-circle-check' : 'fa-circle-exclamation'}`}></i>
                                {alert.message}
                            </span>
                            <button className="btn-close btn-close-sm" onClick={() => setAlert(null)}></button>
                        </div>
                    )}

                    {/* ── Route-like page rendering ── */}
                    {activePage === 'dashboard'    && <Dashboard stats={stats} setActivePage={setActivePage} />}
                    {activePage === 'doctors'      && <DoctorModule triggerAlert={triggerAlert} onSaveSuccess={onSaveSuccess} />}
                    {activePage === 'patients'     && <PatientModule triggerAlert={triggerAlert} onSaveSuccess={onSaveSuccess} />}
                    {activePage === 'appointments' && <AppointmentModule triggerAlert={triggerAlert} onSaveSuccess={onSaveSuccess} />}
                </div>
            </div>
        </div>
    );
}
