import React from 'react';
import ReactDOM from 'react-dom/client';
import App from './App';

// Bootstrap the React SPA — mounts <App /> into <div id="root"> in app.blade.php
ReactDOM.createRoot(document.getElementById('root')).render(
    <React.StrictMode>
        <App />
    </React.StrictMode>
);
