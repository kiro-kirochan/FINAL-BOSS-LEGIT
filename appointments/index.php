<?php
session_start();
require_once '../includes/db.php';
$activePage = 'appointments';

$search = trim($_GET['search'] ?? '');
$filterStatus = trim($_GET['status'] ?? '');

$sql = "SELECT a.*, 
        d.name AS doctor_name, d.specialization,
        p.fname, p.lname, p.email AS patient_email, p.contact
        FROM appointmenttb a
        JOIN doctb d ON a.doctor_id = d.id
        JOIN patreg p ON a.patient_id = p.id
        WHERE 1=1";
$params = [];
if ($search) {
    $sql .= " AND (p.fname LIKE ? OR p.lname LIKE ? OR d.name LIKE ? OR p.email LIKE ?)";
    for ($i=0;$i<4;$i++) $params[] = "%$search%";
}
if ($filterStatus) { $sql .= " AND a.status = ?"; $params[] = $filterStatus; }
$sql .= " ORDER BY a.appdate DESC, a.apptime DESC";
$stmt = $pdo->prepare($sql); $stmt->execute($params);
$appointments = $stmt->fetchAll();

$flash = $_SESSION['flash'] ?? null; unset($_SESSION['flash']);
require_once '../includes/header.php';
?>
<?php if ($flash): ?>
<div class="alert alert-<?= $flash['type'] ?> alert-dismissible fade show mb-4">
  <i class="fa-solid fa-<?= $flash['type']==='success'?'circle-check':'circle-exclamation' ?> me-2"></i>
  <?= htmlspecialchars($flash['msg']) ?>
  <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<div class="card">
  <div class="card-header">
    <div>
      <div class="card-title"><i class="fa-solid fa-calendar-check text-primary me-2"></i>Appointment Management</div>
      <div class="card-subtitle">Book, view, update and cancel appointments</div>
    </div>
    <a href="/hospital/appointments/create.php" class="btn btn-primary btn-sm px-3"><i class="fa-solid fa-plus me-1"></i> New Appointment</a>
  </div>
  <div class="filter-bar">
    <form method="GET" class="row g-2">
      <div class="col-md-7">
        <div class="input-group">
          <span class="input-group-text"><i class="fa-solid fa-magnifying-glass text-muted"></i></span>
          <input type="text" name="search" class="form-control" placeholder="Search by patient or doctor name..." value="<?= htmlspecialchars($search) ?>">
          <?php if ($filterStatus): ?><input type="hidden" name="status" value="<?= htmlspecialchars($filterStatus) ?>"><?php endif; ?>
        </div>
      </div>
      <div class="col-md-4">
        <div class="input-group">
          <span class="input-group-text"><i class="fa-solid fa-filter text-muted"></i></span>
          <select name="status" class="form-select" onchange="this.form.submit()">
            <option value="">All Statuses</option>
            <?php foreach (['Pending','Confirmed','Completed','Cancelled'] as $s): ?>
            <option value="<?= $s ?>" <?= $filterStatus===$s?'selected':'' ?>><?= $s ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>
      <div class="col-md-1"><button type="submit" class="btn btn-primary w-100"><i class="fa-solid fa-magnifying-glass"></i></button></div>
    </form>
  </div>
  <div class="table-responsive">
    <table class="table table-hover mb-0">
      <thead><tr><th class="ps-4">#</th><th>Patient</th><th>Doctor</th><th>Date &amp; Time</th><th>Status</th><th class="text-end pe-4">Actions</th></tr></thead>
      <tbody>
        <?php if (empty($appointments)): ?>
        <tr><td colspan="6" class="text-center py-5"><i class="fa-solid fa-calendar-xmark fa-2x text-muted mb-2 d-block"></i><span class="text-muted">No appointments found.</span></td></tr>
        <?php else: foreach ($appointments as $i => $a): ?>
        <tr>
          <td class="ps-4 text-muted"><?= $i+1 ?></td>
          <td>
            <div class="fw-semibold"><?= htmlspecialchars($a['fname'].' '.$a['lname']) ?></div>
            <div class="text-muted" style="font-size:0.78rem;"><?= htmlspecialchars($a['patient_email']) ?></div>
          </td>
          <td>
            <div class="fw-semibold"><?= htmlspecialchars($a['doctor_name']) ?></div>
            <div class="text-muted" style="font-size:0.78rem;"><?= htmlspecialchars($a['specialization']) ?></div>
          </td>
          <td>
            <div class="fw-semibold"><?= date('M j, Y', strtotime($a['appdate'])) ?></div>
            <div class="text-muted" style="font-size:0.78rem;"><?= date('g:i A', strtotime($a['apptime'])) ?></div>
          </td>
          <td><span class="status-badge status-<?= $a['status'] ?>"><?= $a['status'] ?></span></td>
          <td class="text-end pe-4">
            <div class="d-inline-flex gap-1">
              <a href="/hospital/appointments/edit.php?id=<?= $a['id'] ?>" class="btn btn-sm btn-outline-primary"><i class="fa-solid fa-pencil"></i></a>
              <button class="btn btn-sm btn-outline-danger"
                data-delete-url="/hospital/appointments/delete.php?id=<?= $a['id'] ?>"
                data-delete-name="<?= htmlspecialchars($a['fname'].' '.$a['lname']."'s appointment") ?>">
                <i class="fa-solid fa-trash-can"></i>
              </button>
            </div>
          </td>
        </tr>
        <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
  <?php if (!empty($appointments)): ?>
  <div class="card-footer-note">Showing <strong><?= count($appointments) ?></strong> appointment<?= count($appointments)!==1?'s':'' ?></div>
  <?php endif; ?>
</div>
<?php require_once '../includes/footer.php'; ?>
