<?php
require_once 'includes/db.php';
$activePage = 'dashboard';

// Fetch stats
$stats = [];
$stats['total_doctors']      = $pdo->query("SELECT COUNT(*) FROM doctb")->fetchColumn();
$stats['total_patients']     = $pdo->query("SELECT COUNT(*) FROM patreg")->fetchColumn();
$stats['total_appointments'] = $pdo->query("SELECT COUNT(*) FROM appointmenttb")->fetchColumn();
$stats['pending']   = $pdo->query("SELECT COUNT(*) FROM appointmenttb WHERE status='Pending'")->fetchColumn();
$stats['confirmed'] = $pdo->query("SELECT COUNT(*) FROM appointmenttb WHERE status='Confirmed'")->fetchColumn();
$stats['completed'] = $pdo->query("SELECT COUNT(*) FROM appointmenttb WHERE status='Completed'")->fetchColumn();
$stats['cancelled'] = $pdo->query("SELECT COUNT(*) FROM appointmenttb WHERE status='Cancelled'")->fetchColumn();

require_once 'includes/header.php';
?>

<!-- Welcome Banner -->
<div style="background:linear-gradient(135deg,#1d4ed8,#0d6efd);border-radius:14px;padding:28px 32px;color:#fff;margin-bottom:28px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:16px;">
  <div>
    <h4 style="font-weight:700;margin:0;"><i class="fa-solid fa-hospital me-2"></i>Global Hospital</h4>
    <p style="margin:6px 0 0;opacity:0.8;font-size:0.9rem;">Welcome to the Hospital Management System Admin Panel</p>
  </div>
  <div style="font-size:0.8rem;opacity:0.75;">
    Inspired by kishan0725/Hospital-Management-System<br>
    Rebuilt with HTML + CSS + Bootstrap 5 + PHP + MySQL
  </div>
</div>

<!-- Stat Cards -->
<div class="row g-3 mb-4">
  <div class="col-sm-6 col-xl-3">
    <a href="/hospital/doctors/index.php" class="stat-card">
      <div class="stat-icon" style="background:#dbeafe;"><i class="fa-solid fa-user-doctor" style="color:#1d4ed8;"></i></div>
      <div><div class="stat-value"><?= $stats['total_doctors'] ?></div><div class="stat-label">Total Doctors</div></div>
    </a>
  </div>
  <div class="col-sm-6 col-xl-3">
    <a href="/hospital/patients/index.php" class="stat-card">
      <div class="stat-icon" style="background:#d1fae5;"><i class="fa-solid fa-hospital-user" style="color:#065f46;"></i></div>
      <div><div class="stat-value"><?= $stats['total_patients'] ?></div><div class="stat-label">Total Patients</div></div>
    </a>
  </div>
  <div class="col-sm-6 col-xl-3">
    <a href="/hospital/appointments/index.php" class="stat-card">
      <div class="stat-icon" style="background:#fae8ff;"><i class="fa-solid fa-calendar-check" style="color:#7e22ce;"></i></div>
      <div><div class="stat-value"><?= $stats['total_appointments'] ?></div><div class="stat-label">Total Appointments</div></div>
    </a>
  </div>
  <div class="col-sm-6 col-xl-3">
    <a href="/hospital/appointments/index.php" class="stat-card">
      <div class="stat-icon" style="background:#fef9c3;"><i class="fa-solid fa-clock" style="color:#854d0e;"></i></div>
      <div><div class="stat-value"><?= $stats['pending'] ?></div><div class="stat-label">Pending Appointments</div></div>
    </a>
  </div>
</div>

<!-- Bottom Row -->
<div class="row g-3">
  <!-- Appointment Breakdown -->
  <div class="col-lg-6">
    <div class="card">
      <div class="card-header">
        <div class="card-title"><i class="fa-solid fa-chart-pie me-2 text-primary"></i>Appointment Breakdown</div>
      </div>
      <div class="card-body p-0">
        <?php foreach ([
          ['Confirmed', $stats['confirmed'], 'status-Confirmed'],
          ['Completed', $stats['completed'], 'status-Completed'],
          ['Cancelled', $stats['cancelled'], 'status-Cancelled'],
          ['Pending',   $stats['pending'],   'status-Pending'],
        ] as [$label, $val, $cls]): ?>
        <div style="display:flex;align-items:center;justify-content:space-between;padding:12px 22px;border-bottom:1px solid #f0f4f8;">
          <span class="status-badge <?= $cls ?>"><?= $label ?></span>
          <strong style="font-size:1.1rem;"><?= $val ?></strong>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>

  <!-- Quick Access -->
  <div class="col-lg-6">
    <div class="card">
      <div class="card-header">
        <div class="card-title"><i class="fa-solid fa-bolt me-2 text-primary"></i>Quick Access</div>
      </div>
      <div class="card-body d-flex flex-column gap-3">
        <?php foreach ([
          ['doctors',      'fa-user-doctor',    'Manage Doctors',      'Add, edit or remove doctors'],
          ['patients',     'fa-hospital-user',  'Manage Patients',     'Register and manage patients'],
          ['appointments', 'fa-calendar-check', 'Manage Appointments', 'Book and track appointments'],
        ] as [$page, $icon, $label, $desc]): ?>
        <a href="/hospital/<?= $page ?>/index.php" class="btn btn-outline-primary text-start d-flex align-items-center gap-3" style="padding:12px 16px;">
          <i class="fa-solid <?= $icon ?> fs-5"></i>
          <div>
            <div style="font-weight:600;"><?= $label ?></div>
            <div style="font-size:0.78rem;opacity:0.7;"><?= $desc ?></div>
          </div>
        </a>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</div>

<?php require_once 'includes/footer.php'; ?>
