<?php
$pageTitles = [
    'dashboard'    => 'Dashboard Overview',
    'doctors'      => 'Doctor Management',
    'patients'     => 'Patient Management',
    'appointments' => 'Appointment Management',
];
$pageIcons = [
    'dashboard'    => 'fa-gauge',
    'doctors'      => 'fa-user-doctor',
    'patients'     => 'fa-hospital-user',
    'appointments' => 'fa-calendar-check',
];
$title = $pageTitles[$activePage] ?? 'Hospital';
$icon  = $pageIcons[$activePage]  ?? 'fa-hospital';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Global Hospital — <?= htmlspecialchars($title) ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
<style>
:root{--primary:#0d6efd;--sidebar-bg:#1a2236;--sidebar-width:240px}
*{box-sizing:border-box}
body{font-family:'Outfit',sans-serif;background:#f0f4f8;color:#2b3a4a;margin:0}
#sidebar{width:var(--sidebar-width);min-height:100vh;background:var(--sidebar-bg);position:fixed;top:0;left:0;display:flex;flex-direction:column;z-index:200}
.sidebar-brand{padding:22px 20px 16px;border-bottom:1px solid rgba(255,255,255,0.08)}
.sidebar-brand h5{color:#fff;font-weight:700;margin:0;font-size:1rem}
.sidebar-brand p{color:rgba(255,255,255,0.45);font-size:0.72rem;margin:2px 0 0}
.sidebar-nav{padding:14px 0;flex:1}
.sidebar-nav .nav-label{padding:8px 20px 4px;font-size:0.65rem;text-transform:uppercase;letter-spacing:1.2px;color:rgba(255,255,255,0.3);font-weight:700}
.sidebar-nav a{display:flex;align-items:center;gap:10px;padding:10px 20px;color:rgba(255,255,255,0.65);text-decoration:none;font-size:0.88rem;font-weight:500;border-left:3px solid transparent;transition:all 0.18s}
.sidebar-nav a:hover,.sidebar-nav a.active{color:#fff;background:rgba(255,255,255,0.07);border-left-color:var(--primary)}
.sidebar-nav a i{width:18px;text-align:center;font-size:0.9rem}
#main-content{margin-left:var(--sidebar-width);min-height:100vh;display:flex;flex-direction:column}
#topbar{background:#fff;border-bottom:1px solid #e8edf2;padding:12px 28px;display:flex;align-items:center;justify-content:space-between;position:sticky;top:0;z-index:100;box-shadow:0 1px 6px rgba(0,0,0,0.06)}
#topbar .page-title{font-size:1.05rem;font-weight:700;color:#1e293b}
#topbar .topbar-right{display:flex;align-items:center;gap:14px;font-size:0.85rem;color:#718096}
.page-body{padding:28px;flex:1}
.card{background:#fff;border-radius:12px;border:none;box-shadow:0 2px 14px rgba(0,0,0,0.06)}
.card-header{background:#fff;border-bottom:1px solid #edf2f7;border-radius:12px 12px 0 0!important;padding:18px 22px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px}
.card-header .card-title{font-weight:700;font-size:1rem;margin:0;color:#1e293b}
.card-header .card-subtitle{font-size:0.78rem;color:#94a3b8;margin-top:2px}
.stat-card{background:#fff;border-radius:12px;padding:20px 22px;box-shadow:0 2px 14px rgba(0,0,0,0.06);display:flex;align-items:center;gap:16px;cursor:pointer;text-decoration:none;color:inherit;transition:transform 0.15s}
.stat-card:hover{transform:translateY(-2px);color:inherit}
.stat-icon{width:48px;height:48px;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:1.3rem;flex-shrink:0}
.stat-value{font-size:1.7rem;font-weight:700;line-height:1;color:#1e293b}
.stat-label{font-size:0.78rem;color:#94a3b8;margin-top:3px}
.btn{border-radius:8px;font-weight:500;transition:transform 0.15s}
.btn:hover{transform:translateY(-1px)}
.table th{font-size:0.72rem;font-weight:700;text-transform:uppercase;letter-spacing:0.7px;color:#94a3b8;background:#f8fafc;padding:12px 16px;border-bottom:1px solid #edf2f7}
.table td{padding:13px 16px;vertical-align:middle;border-bottom:1px solid #f0f4f8;font-size:0.88rem}
.table tbody tr:last-child td{border-bottom:none}
.table-hover tbody tr:hover td{background:#f8fafc}
.avatar{width:38px;height:38px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:0.82rem;flex-shrink:0}
.status-badge{display:inline-flex;align-items:center;gap:5px;padding:4px 10px;border-radius:20px;font-size:0.75rem;font-weight:600}
.status-badge::before{content:'';width:6px;height:6px;border-radius:50%;display:inline-block}
.status-Pending{background:#fef9c3;color:#854d0e}.status-Pending::before{background:#ca8a04}
.status-Confirmed{background:#dcfce7;color:#166534}.status-Confirmed::before{background:#16a34a}
.status-Completed{background:#dbeafe;color:#1e40af}.status-Completed::before{background:#2563eb}
.status-Cancelled{background:#fee2e2;color:#991b1b}.status-Cancelled::before{background:#dc2626}
.alert{border-radius:10px;border:none;font-size:0.88rem}
.filter-bar{background:#f8fafc;border-bottom:1px solid #edf2f7;padding:14px 22px}
.input-group-text{background:#fff;border-color:#dee2e6}
.form-control,.form-select{border-color:#dee2e6;font-size:0.88rem}
.form-control:focus,.form-select:focus{border-color:var(--primary);box-shadow:0 0 0 3px rgba(13,110,253,0.1)}
.modal-content{border-radius:14px;border:none}
.form-label{font-weight:600;font-size:0.85rem;color:#374151;margin-bottom:5px}
.card-footer-note{border-top:1px solid #f0f4f8;padding:10px 22px;background:#fff;font-size:0.78rem;color:#94a3b8;border-radius:0 0 12px 12px}
</style>
</head>
<body>
<div id="sidebar">
  <div class="sidebar-brand">
    <div class="d-flex align-items-center gap-2">
      <div style="width:36px;height:36px;border-radius:10px;background:#0d6efd;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
        <i class="fa-solid fa-hospital" style="color:#fff;font-size:1rem;"></i>
      </div>
      <div><h5>Global Hospital</h5><p>Management System</p></div>
    </div>
  </div>
  <div class="sidebar-nav">
    <div class="nav-label">Main Menu</div>
    <a href="/hospital/index.php"              class="<?= $activePage==='dashboard'    ?'active':'' ?>"><i class="fa-solid fa-gauge"></i> Dashboard</a>
    <a href="/hospital/doctors/index.php"      class="<?= $activePage==='doctors'      ?'active':'' ?>"><i class="fa-solid fa-user-doctor"></i> Doctors</a>
    <a href="/hospital/patients/index.php"     class="<?= $activePage==='patients'     ?'active':'' ?>"><i class="fa-solid fa-hospital-user"></i> Patients</a>
    <a href="/hospital/appointments/index.php" class="<?= $activePage==='appointments' ?'active':'' ?>"><i class="fa-solid fa-calendar-check"></i> Appointments</a>
  </div>
  <div style="padding:16px 20px;border-top:1px solid rgba(255,255,255,0.08);">
    <div style="font-size:0.72rem;color:rgba(255,255,255,0.3);">Web Systems &amp; Technologies<br>Final Project — A.Y. 2025-2026</div>
  </div>
</div>
<div id="main-content">
  <div id="topbar">
    <div class="page-title"><i class="fa-solid <?= $icon ?> me-2 text-primary"></i><?= htmlspecialchars($title) ?></div>
    <div class="topbar-right">
      <span><i class="fa-regular fa-calendar me-1"></i><?= date('l, F j, Y') ?></span>
      <span style="background:#0d6efd;color:#fff;width:32px;height:32px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:0.82rem;">AD</span>
    </div>
  </div>
  <div class="page-body">
