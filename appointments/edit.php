<?php
session_start();
require_once '../includes/db.php';
$activePage = 'appointments';
$errors = [];

$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT * FROM appointmenttb WHERE id = ?");
$stmt->execute([$id]);
$appt = $stmt->fetch();
if (!$appt) { $_SESSION['flash'] = ['type'=>'danger','msg'=>'Appointment not found.']; header('Location: /hospital/appointments/index.php'); exit; }

$doctors  = $pdo->query("SELECT id, name, specialization FROM doctb ORDER BY name")->fetchAll();
$patients = $pdo->query("SELECT id, fname, lname FROM patreg ORDER BY fname, lname")->fetchAll();

$form = [
    'patient_id' => $appt['patient_id'],
    'doctor_id'  => $appt['doctor_id'],
    'appdate'    => substr($appt['appdate'], 0, 10),
    'apptime'    => substr($appt['apptime'], 0, 5),
    'status'     => $appt['status'],
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $form = [
        'patient_id' => (int)($_POST['patient_id'] ?? 0),
        'doctor_id'  => (int)($_POST['doctor_id']  ?? 0),
        'appdate'    => trim($_POST['appdate']  ?? ''),
        'apptime'    => trim($_POST['apptime']  ?? ''),
        'status'     => trim($_POST['status']   ?? 'Pending'),
    ];
    if (!$form['patient_id']) $errors['patient_id'] = 'Please select a patient.';
    if (!$form['doctor_id'])  $errors['doctor_id']  = 'Please select a doctor.';
    if (!$form['appdate'])    $errors['appdate']     = 'Appointment date is required.';
    if (!$form['apptime'])    $errors['apptime']     = 'Appointment time is required.';
    if (!in_array($form['status'], ['Pending','Confirmed','Completed','Cancelled'])) $errors['status'] = 'Invalid status.';

    // Conflict detection — exclude current appointment
    if (empty($errors)) {
        $conflict = $pdo->prepare("SELECT COUNT(*) FROM appointmenttb WHERE doctor_id=? AND appdate=? AND apptime=? AND status IN ('Pending','Confirmed') AND id != ?");
        $conflict->execute([$form['doctor_id'], $form['appdate'], $form['apptime'], $id]);
        if ($conflict->fetchColumn()) $errors['apptime'] = 'That doctor already has a Pending or Confirmed appointment at this date and time.';
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare("UPDATE appointmenttb SET patient_id=?, doctor_id=?, appdate=?, apptime=?, status=? WHERE id=?");
        $stmt->execute([$form['patient_id'], $form['doctor_id'], $form['appdate'], $form['apptime'], $form['status'], $id]);
        $_SESSION['flash'] = ['type'=>'success','msg'=>'Appointment updated successfully!'];
        header('Location: /hospital/appointments/index.php'); exit;
    }
}

require_once '../includes/header.php';
?>
<div class="card">
  <div class="card-header">
    <div>
      <div class="card-title" style="color:#0d6efd;"><i class="fa-solid fa-calendar-pen me-2"></i>Edit Appointment</div>
      <div class="card-subtitle">Update appointment details below.</div>
    </div>
    <a href="/hospital/appointments/index.php" class="btn btn-sm btn-light"><i class="fa-solid fa-arrow-left me-1"></i> Back</a>
  </div>
  <div class="card-body p-4">
    <form method="POST" novalidate>
      <div class="row g-3">

        <div class="col-md-6">
          <label class="form-label">Patient <span class="text-danger">*</span></label>
          <div class="input-group">
            <span class="input-group-text"><i class="fa-solid fa-hospital-user text-muted"></i></span>
            <select name="patient_id" class="form-select <?= isset($errors['patient_id'])?'is-invalid':'' ?>">
              <option value="">-- Select Patient --</option>
              <?php foreach ($patients as $p): ?>
              <option value="<?= $p['id'] ?>" <?= $form['patient_id']==$p['id']?'selected':'' ?>><?= htmlspecialchars($p['fname'].' '.$p['lname']) ?></option>
              <?php endforeach; ?>
            </select>
            <?php if (isset($errors['patient_id'])): ?><div class="invalid-feedback"><?= $errors['patient_id'] ?></div><?php endif; ?>
          </div>
        </div>

        <div class="col-md-6">
          <label class="form-label">Doctor <span class="text-danger">*</span></label>
          <div class="input-group">
            <span class="input-group-text"><i class="fa-solid fa-user-doctor text-muted"></i></span>
            <select name="doctor_id" class="form-select <?= isset($errors['doctor_id'])?'is-invalid':'' ?>">
              <option value="">-- Select Doctor --</option>
              <?php foreach ($doctors as $d): ?>
              <option value="<?= $d['id'] ?>" <?= $form['doctor_id']==$d['id']?'selected':'' ?>><?= htmlspecialchars($d['name'].' ('.$d['specialization'].')') ?></option>
              <?php endforeach; ?>
            </select>
            <?php if (isset($errors['doctor_id'])): ?><div class="invalid-feedback"><?= $errors['doctor_id'] ?></div><?php endif; ?>
          </div>
        </div>

        <div class="col-md-6">
          <label class="form-label">Appointment Date <span class="text-danger">*</span></label>
          <div class="input-group">
            <span class="input-group-text"><i class="fa-solid fa-calendar text-muted"></i></span>
            <input type="date" name="appdate" class="form-control <?= isset($errors['appdate'])?'is-invalid':'' ?>" value="<?= htmlspecialchars($form['appdate']) ?>">
            <?php if (isset($errors['appdate'])): ?><div class="invalid-feedback"><?= $errors['appdate'] ?></div><?php endif; ?>
          </div>
        </div>

        <div class="col-md-6">
          <label class="form-label">Appointment Time <span class="text-danger">*</span></label>
          <div class="input-group">
            <span class="input-group-text"><i class="fa-solid fa-clock text-muted"></i></span>
            <input type="time" name="apptime" class="form-control <?= isset($errors['apptime'])?'is-invalid':'' ?>" value="<?= htmlspecialchars($form['apptime']) ?>">
            <?php if (isset($errors['apptime'])): ?><div class="invalid-feedback"><?= $errors['apptime'] ?></div><?php endif; ?>
          </div>
        </div>

        <div class="col-md-6">
          <label class="form-label">Status</label>
          <div class="input-group">
            <span class="input-group-text"><i class="fa-solid fa-circle-dot text-muted"></i></span>
            <select name="status" class="form-select">
              <?php foreach (['Pending','Confirmed','Completed','Cancelled'] as $s): ?>
              <option value="<?= $s ?>" <?= $form['status']===$s?'selected':'' ?>><?= $s ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>

      </div>
      <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
        <a href="/hospital/appointments/index.php" class="btn btn-light">Cancel</a>
        <button type="submit" class="btn btn-primary px-4"><i class="fa-solid fa-floppy-disk me-1"></i> Update Appointment</button>
      </div>
    </form>
  </div>
</div>
<?php require_once '../includes/footer.php'; ?>
