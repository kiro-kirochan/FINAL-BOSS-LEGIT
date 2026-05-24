<?php
session_start();
require_once '../includes/db.php';
$activePage = 'doctors';
$errors = [];
$form = ['name'=>'','email'=>'','password'=>'','specialization'=>'','docFees'=>''];
$specs = $pdo->query("SELECT specialization FROM specialtb ORDER BY specialization")->fetchAll(PDO::FETCH_COLUMN);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $form = [
        'name'           => trim($_POST['name'] ?? ''),
        'email'          => trim($_POST['email'] ?? ''),
        'password'       => $_POST['password'] ?? '',
        'specialization' => trim($_POST['specialization'] ?? ''),
        'docFees'        => trim($_POST['docFees'] ?? ''),
    ];
    if (!$form['name'])           $errors['name']           = 'Full name is required.';
    if (!$form['email'])          $errors['email']          = 'Email is required.';
    elseif (!filter_var($form['email'], FILTER_VALIDATE_EMAIL)) $errors['email'] = 'Invalid email format.';
    else {
        $chk = $pdo->prepare("SELECT COUNT(*) FROM doctb WHERE email = ?");
        $chk->execute([$form['email']]);
        if ($chk->fetchColumn()) $errors['email'] = 'Email is already taken.';
    }
    if (!$form['password'])       $errors['password']       = 'Password is required.';
    elseif (strlen($form['password']) < 6) $errors['password'] = 'Password must be at least 6 characters.';
    if (!$form['specialization']) $errors['specialization'] = 'Specialization is required.';
    if (!$form['docFees'] && $form['docFees'] !== '0') $errors['docFees'] = 'Consultation fee is required.';
    elseif (!is_numeric($form['docFees']) || $form['docFees'] < 0) $errors['docFees'] = 'Fee must be a valid positive number.';

    if (empty($errors)) {
        $stmt = $pdo->prepare("INSERT INTO doctb (name, email, password, specialization, docFees) VALUES (?,?,?,?,?)");
        $stmt->execute([$form['name'], $form['email'], password_hash($form['password'], PASSWORD_BCRYPT), $form['specialization'], $form['docFees']]);
        $_SESSION['flash'] = ['type'=>'success','msg'=>'Doctor registered successfully!'];
        header('Location: /hospital/doctors/index.php'); exit;
    }
}

require_once '../includes/header.php';
?>
<div class="card">
  <div class="card-header">
    <div>
      <div class="card-title" style="color:#0d6efd;"><i class="fa-solid fa-user-plus me-2"></i>Register New Doctor</div>
      <div class="card-subtitle">Fill in all fields to add a new doctor.</div>
    </div>
    <a href="/hospital/doctors/index.php" class="btn btn-sm btn-light"><i class="fa-solid fa-arrow-left me-1"></i> Back</a>
  </div>
  <div class="card-body p-4">
    <form method="POST" novalidate>
      <div class="row g-3">

        <div class="col-md-6">
          <label class="form-label">Full Name <span class="text-danger">*</span></label>
          <div class="input-group">
            <span class="input-group-text"><i class="fa-solid fa-user text-muted"></i></span>
            <input type="text" name="name" class="form-control <?= isset($errors['name'])?'is-invalid':'' ?>" placeholder="e.g. Dr. Jane Doe" value="<?= htmlspecialchars($form['name']) ?>">
            <?php if (isset($errors['name'])): ?><div class="invalid-feedback"><?= $errors['name'] ?></div><?php endif; ?>
          </div>
        </div>

        <div class="col-md-6">
          <label class="form-label">Email Address <span class="text-danger">*</span></label>
          <div class="input-group">
            <span class="input-group-text"><i class="fa-solid fa-envelope text-muted"></i></span>
            <input type="email" name="email" class="form-control <?= isset($errors['email'])?'is-invalid':'' ?>" placeholder="doctor@hospital.com" value="<?= htmlspecialchars($form['email']) ?>">
            <?php if (isset($errors['email'])): ?><div class="invalid-feedback"><?= $errors['email'] ?></div><?php endif; ?>
          </div>
        </div>

        <div class="col-md-6">
          <label class="form-label">Password <span class="text-danger">*</span></label>
          <div class="input-group">
            <span class="input-group-text"><i class="fa-solid fa-key text-muted"></i></span>
            <input type="password" name="password" class="form-control <?= isset($errors['password'])?'is-invalid':'' ?>" placeholder="Min. 6 characters">
            <?php if (isset($errors['password'])): ?><div class="invalid-feedback"><?= $errors['password'] ?></div><?php endif; ?>
          </div>
        </div>

        <div class="col-md-6">
          <label class="form-label">Specialization <span class="text-danger">*</span></label>
          <div class="input-group">
            <span class="input-group-text"><i class="fa-solid fa-stethoscope text-muted"></i></span>
            <select name="specialization" class="form-select <?= isset($errors['specialization'])?'is-invalid':'' ?>">
              <option value="">-- Select Specialization --</option>
              <?php foreach ($specs as $s): ?>
              <option value="<?= htmlspecialchars($s) ?>" <?= $form['specialization']===$s?'selected':'' ?>><?= htmlspecialchars($s) ?></option>
              <?php endforeach; ?>
            </select>
            <?php if (isset($errors['specialization'])): ?><div class="invalid-feedback"><?= $errors['specialization'] ?></div><?php endif; ?>
          </div>
        </div>

        <div class="col-md-6">
          <label class="form-label">Consultation Fee ($) <span class="text-danger">*</span></label>
          <div class="input-group">
            <span class="input-group-text"><i class="fa-solid fa-dollar-sign text-muted"></i></span>
            <input type="number" name="docFees" step="0.01" min="0" class="form-control <?= isset($errors['docFees'])?'is-invalid':'' ?>" placeholder="e.g. 250.00" value="<?= htmlspecialchars($form['docFees']) ?>">
            <?php if (isset($errors['docFees'])): ?><div class="invalid-feedback"><?= $errors['docFees'] ?></div><?php endif; ?>
          </div>
        </div>

      </div>
      <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
        <a href="/hospital/doctors/index.php" class="btn btn-light">Cancel</a>
        <button type="submit" class="btn btn-primary px-4"><i class="fa-solid fa-floppy-disk me-1"></i> Register Doctor</button>
      </div>
    </form>
  </div>
</div>
<?php require_once '../includes/footer.php'; ?>