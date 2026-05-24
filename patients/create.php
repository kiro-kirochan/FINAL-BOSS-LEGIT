<?php
session_start();
require_once '../includes/db.php';
$activePage = 'patients';
$errors = [];
$form = ['fname'=>'','lname'=>'','email'=>'','password'=>'','contact'=>'','gender'=>''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $form = [
        'fname'    => trim($_POST['fname']    ?? ''),
        'lname'    => trim($_POST['lname']    ?? ''),
        'email'    => trim($_POST['email']    ?? ''),
        'password' => $_POST['password']      ?? '',
        'contact'  => trim($_POST['contact']  ?? ''),
        'gender'   => trim($_POST['gender']   ?? ''),
    ];
    if (!$form['fname'])    $errors['fname']    = 'First name is required.';
    if (!$form['lname'])    $errors['lname']    = 'Last name is required.';
    if (!$form['email'])    $errors['email']    = 'Email is required.';
    elseif (!filter_var($form['email'], FILTER_VALIDATE_EMAIL)) $errors['email'] = 'Invalid email format.';
    else {
        $chk = $pdo->prepare("SELECT COUNT(*) FROM patreg WHERE email = ?");
        $chk->execute([$form['email']]);
        if ($chk->fetchColumn()) $errors['email'] = 'Email is already registered.';
    }
    if (!$form['password'])          $errors['password'] = 'Password is required.';
    elseif (strlen($form['password']) < 6) $errors['password'] = 'Password must be at least 6 characters.';
    if (!$form['contact'])           $errors['contact']  = 'Contact number is required.';
    if (!in_array($form['gender'], ['Male','Female','Other'])) $errors['gender'] = 'Please select a valid gender.';

    if (empty($errors)) {
        $stmt = $pdo->prepare("INSERT INTO patreg (fname, lname, email, password, contact, gender) VALUES (?,?,?,?,?,?)");
        $stmt->execute([$form['fname'], $form['lname'], $form['email'], password_hash($form['password'], PASSWORD_BCRYPT), $form['contact'], $form['gender']]);
        $_SESSION['flash'] = ['type'=>'success','msg'=>'Patient registered successfully!'];
        header('Location: /hospital/patients/index.php'); exit;
    }
}

require_once '../includes/header.php';
?>
<div class="card">
  <div class="card-header">
    <div>
      <div class="card-title" style="color:#0d6efd;"><i class="fa-solid fa-user-plus me-2"></i>Register New Patient</div>
      <div class="card-subtitle">Fill in all required fields to register a patient.</div>
    </div>
    <a href="/hospital/patients/index.php" class="btn btn-sm btn-light"><i class="fa-solid fa-arrow-left me-1"></i> Back</a>
  </div>
  <div class="card-body p-4">
    <form method="POST" novalidate>
      <div class="row g-3">

        <div class="col-md-6">
          <label class="form-label">First Name <span class="text-danger">*</span></label>
          <div class="input-group">
            <span class="input-group-text"><i class="fa-solid fa-user text-muted"></i></span>
            <input type="text" name="fname" class="form-control <?= isset($errors['fname'])?'is-invalid':'' ?>" placeholder="First name" value="<?= htmlspecialchars($form['fname']) ?>">
            <?php if (isset($errors['fname'])): ?><div class="invalid-feedback"><?= $errors['fname'] ?></div><?php endif; ?>
          </div>
        </div>

        <div class="col-md-6">
          <label class="form-label">Last Name <span class="text-danger">*</span></label>
          <div class="input-group">
            <span class="input-group-text"><i class="fa-solid fa-user text-muted"></i></span>
            <input type="text" name="lname" class="form-control <?= isset($errors['lname'])?'is-invalid':'' ?>" placeholder="Last name" value="<?= htmlspecialchars($form['lname']) ?>">
            <?php if (isset($errors['lname'])): ?><div class="invalid-feedback"><?= $errors['lname'] ?></div><?php endif; ?>
          </div>
        </div>

        <div class="col-md-6">
          <label class="form-label">Email Address <span class="text-danger">*</span></label>
          <div class="input-group">
            <span class="input-group-text"><i class="fa-solid fa-envelope text-muted"></i></span>
            <input type="email" name="email" class="form-control <?= isset($errors['email'])?'is-invalid':'' ?>" placeholder="patient@email.com" value="<?= htmlspecialchars($form['email']) ?>">
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
          <label class="form-label">Contact Number <span class="text-danger">*</span></label>
          <div class="input-group">
            <span class="input-group-text"><i class="fa-solid fa-phone text-muted"></i></span>
            <input type="text" name="contact" class="form-control <?= isset($errors['contact'])?'is-invalid':'' ?>" placeholder="e.g. 09171234567" value="<?= htmlspecialchars($form['contact']) ?>">
            <?php if (isset($errors['contact'])): ?><div class="invalid-feedback"><?= $errors['contact'] ?></div><?php endif; ?>
          </div>
        </div>

        <div class="col-md-6">
          <label class="form-label">Gender <span class="text-danger">*</span></label>
          <div class="input-group">
            <span class="input-group-text"><i class="fa-solid fa-venus-mars text-muted"></i></span>
            <select name="gender" class="form-select <?= isset($errors['gender'])?'is-invalid':'' ?>">
              <option value="">-- Select Gender --</option>
              <option value="Male"   <?= $form['gender']==='Male'  ?'selected':'' ?>>Male</option>
              <option value="Female" <?= $form['gender']==='Female'?'selected':'' ?>>Female</option>
              <option value="Other"  <?= $form['gender']==='Other' ?'selected':'' ?>>Other</option>
            </select>
            <?php if (isset($errors['gender'])): ?><div class="invalid-feedback"><?= $errors['gender'] ?></div><?php endif; ?>
          </div>
        </div>

      </div>
      <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
        <a href="/hospital/patients/index.php" class="btn btn-light">Cancel</a>
        <button type="submit" class="btn btn-primary px-4"><i class="fa-solid fa-floppy-disk me-1"></i> Register Patient</button>
      </div>
    </form>
  </div>
</div>
<?php require_once '../includes/footer.php'; ?>
