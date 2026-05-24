<?php
session_start();
require_once '../includes/db.php';
$activePage = 'patients';

$search = trim($_GET['search'] ?? '');
$filterGender = trim($_GET['gender'] ?? '');

$sql = "SELECT * FROM patreg WHERE 1=1";
$params = [];
if ($search) { $sql .= " AND (fname LIKE ? OR lname LIKE ? OR email LIKE ? OR contact LIKE ?)"; for($i=0;$i<4;$i++) $params[] = "%$search%"; }
if ($filterGender) { $sql .= " AND gender = ?"; $params[] = $filterGender; }
$sql .= " ORDER BY fname, lname";
$stmt = $pdo->prepare($sql); $stmt->execute($params);
$patients = $stmt->fetchAll();
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
      <div class="card-title"><i class="fa-solid fa-hospital-user text-primary me-2"></i>Patient Registry</div>
      <div class="card-subtitle">Register, manage and search patient records</div>
    </div>
    <a href="/hospital/patients/create.php" class="btn btn-primary btn-sm px-3"><i class="fa-solid fa-plus me-1"></i> Add New Patient</a>
  </div>
  <div class="filter-bar">
    <form method="GET" class="row g-2">
      <div class="col-md-7">
        <div class="input-group">
          <span class="input-group-text"><i class="fa-solid fa-magnifying-glass text-muted"></i></span>
          <input type="text" name="search" class="form-control" placeholder="Search by name, email or contact..." value="<?= htmlspecialchars($search) ?>">
          <?php if ($filterGender): ?><input type="hidden" name="gender" value="<?= htmlspecialchars($filterGender) ?>"><?php endif; ?>
        </div>
      </div>
      <div class="col-md-4">
        <div class="input-group">
          <span class="input-group-text"><i class="fa-solid fa-venus-mars text-muted"></i></span>
          <select name="gender" class="form-select" onchange="this.form.submit()">
            <option value="">All Genders</option>
            <option value="Male"   <?= $filterGender==='Male'  ?'selected':'' ?>>Male</option>
            <option value="Female" <?= $filterGender==='Female'?'selected':'' ?>>Female</option>
            <option value="Other"  <?= $filterGender==='Other' ?'selected':'' ?>>Other</option>
          </select>
        </div>
      </div>
      <div class="col-md-1"><button type="submit" class="btn btn-primary w-100"><i class="fa-solid fa-magnifying-glass"></i></button></div>
    </form>
  </div>
  <div class="table-responsive">
    <table class="table table-hover mb-0">
      <thead><tr><th class="ps-4">#</th><th>Patient</th><th>Contact</th><th>Gender</th><th>Registered On</th><th class="text-end pe-4">Actions</th></tr></thead>
      <tbody>
        <?php
        $gBg   = ['Male'=>'#dbeafe','Female'=>'#fce7f3','Other'=>'#fef9c3'];
        $gText = ['Male'=>'#1d4ed8','Female'=>'#be185d','Other'=>'#854d0e'];
        if (empty($patients)): ?>
        <tr><td colspan="6" class="text-center py-5"><i class="fa-solid fa-user-slash fa-2x text-muted mb-2 d-block"></i><span class="text-muted">No patients found.</span></td></tr>
        <?php else: foreach ($patients as $i => $p):
          $bg   = $gBg[$p['gender']]   ?? '#f1f5f9';
          $tc   = $gText[$p['gender']] ?? '#64748b';
          $init = strtoupper(substr($p['fname'],0,1).substr($p['lname'],0,1)); ?>
        <tr>
          <td class="ps-4 text-muted"><?= $i+1 ?></td>
          <td>
            <div class="d-flex align-items-center gap-2">
              <div class="avatar" style="background:<?= $bg ?>;color:<?= $tc ?>;"><?= htmlspecialchars($init) ?></div>
              <div><div class="fw-semibold"><?= htmlspecialchars($p['fname'].' '.$p['lname']) ?></div><div class="text-muted" style="font-size:0.78rem;"><?= htmlspecialchars($p['email']) ?></div></div>
            </div>
          </td>
          <td><?= htmlspecialchars($p['contact']) ?></td>
          <td><span class="badge rounded-pill px-3 py-1" style="background:<?= $bg ?>;color:<?= $tc ?>;"><?= htmlspecialchars($p['gender']) ?></span></td>
          <td class="text-muted"><?= date('M j, Y', strtotime($p['created_at'])) ?></td>
          <td class="text-end pe-4">
            <div class="d-inline-flex gap-1">
              <a href="/hospital/patients/edit.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-outline-primary"><i class="fa-solid fa-pencil"></i></a>
              <button class="btn btn-sm btn-outline-danger" data-delete-url="/hospital/patients/delete.php?id=<?= $p['id'] ?>" data-delete-name="<?= htmlspecialchars($p['fname'].' '.$p['lname']) ?>"><i class="fa-solid fa-trash-can"></i></button>
            </div>
          </td>
        </tr>
        <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
  <?php if (!empty($patients)): ?>
  <div class="card-footer-note">Showing <strong><?= count($patients) ?></strong> patient<?= count($patients)!==1?'s':'' ?></div>
  <?php endif; ?>
</div>
<?php require_once '../includes/footer.php'; ?>
