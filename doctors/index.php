<?php
session_start();
require_once '../includes/db.php';
$activePage = 'doctors';

$search = trim($_GET['search'] ?? '');
$filterSpec = trim($_GET['specialization'] ?? '');

$sql = "SELECT * FROM doctb WHERE 1=1";
$params = [];
if ($search) { $sql .= " AND (name LIKE ? OR email LIKE ?)"; $params[] = "%$search%"; $params[] = "%$search%"; }
if ($filterSpec) { $sql .= " AND specialization = ?"; $params[] = $filterSpec; }
$sql .= " ORDER BY name";
$stmt = $pdo->prepare($sql); $stmt->execute($params);
$doctors = $stmt->fetchAll();
$specs = $pdo->query("SELECT DISTINCT specialization FROM doctb ORDER BY specialization")->fetchAll(PDO::FETCH_COLUMN);
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
      <div class="card-title"><i class="fa-solid fa-user-doctor text-primary me-2"></i>Medical Staff Directory</div>
      <div class="card-subtitle">Create, read, update and delete doctor profiles</div>
    </div>
    <a href="/hospital/doctors/create.php" class="btn btn-primary btn-sm px-3"><i class="fa-solid fa-plus me-1"></i> Add New Doctor</a>
  </div>
  <div class="filter-bar">
    <form method="GET" class="row g-2">
      <div class="col-md-7">
        <div class="input-group">
          <span class="input-group-text"><i class="fa-solid fa-magnifying-glass text-muted"></i></span>
          <input type="text" name="search" class="form-control" placeholder="Search by name or email..." value="<?= htmlspecialchars($search) ?>">
          <?php if ($filterSpec): ?><input type="hidden" name="specialization" value="<?= htmlspecialchars($filterSpec) ?>"><?php endif; ?>
        </div>
      </div>
      <div class="col-md-4">
        <div class="input-group">
          <span class="input-group-text"><i class="fa-solid fa-filter text-muted"></i></span>
          <select name="specialization" class="form-select" onchange="this.form.submit()">
            <option value="">All Specializations</option>
            <?php foreach ($specs as $s): ?>
            <option value="<?= htmlspecialchars($s) ?>" <?= $filterSpec===$s?'selected':'' ?>><?= htmlspecialchars($s) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>
      <div class="col-md-1"><button type="submit" class="btn btn-primary w-100"><i class="fa-solid fa-magnifying-glass"></i></button></div>
    </form>
  </div>
  <div class="table-responsive">
    <table class="table table-hover mb-0">
      <thead><tr><th class="ps-4">#</th><th>Doctor</th><th>Specialization</th><th>Consultation Fee</th><th>Date Added</th><th class="text-end pe-4">Actions</th></tr></thead>
      <tbody>
        <?php if (empty($doctors)): ?>
        <tr><td colspan="6" class="text-center py-5"><i class="fa-solid fa-user-slash fa-2x text-muted mb-2 d-block"></i><span class="text-muted">No doctors found.</span></td></tr>
        <?php else: foreach ($doctors as $i => $doc):
          $initials = strtoupper(substr(preg_replace('/Dr\.\s*/i','', $doc['name']), 0, 2)); ?>
        <tr>
          <td class="ps-4 text-muted"><?= $i+1 ?></td>
          <td>
            <div class="d-flex align-items-center gap-2">
              <div class="avatar" style="background:#dbeafe;color:#1d4ed8;"><?= htmlspecialchars($initials) ?></div>
              <div><div class="fw-semibold"><?= htmlspecialchars($doc['name']) ?></div><div class="text-muted" style="font-size:0.78rem;"><?= htmlspecialchars($doc['email']) ?></div></div>
            </div>
          </td>
          <td><span class="badge rounded-pill bg-primary-subtle text-primary border border-primary-subtle px-3 py-1"><?= htmlspecialchars($doc['specialization']) ?></span></td>
          <td class="fw-semibold">$<?= number_format($doc['docFees'],2) ?></td>
          <td class="text-muted"><?= date('M j, Y', strtotime($doc['created_at'])) ?></td>
          <td class="text-end pe-4">
            <div class="d-inline-flex gap-1">
              <a href="/hospital/doctors/edit.php?id=<?= $doc['id'] ?>" class="btn btn-sm btn-outline-primary"><i class="fa-solid fa-pencil"></i></a>
              <button class="btn btn-sm btn-outline-danger" data-delete-url="/hospital/doctors/delete.php?id=<?= $doc['id'] ?>" data-delete-name="<?= htmlspecialchars($doc['name']) ?>"><i class="fa-solid fa-trash-can"></i></button>
            </div>
          </td>
        </tr>
        <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
  <?php if (!empty($doctors)): ?>
  <div class="card-footer-note">Showing <strong><?= count($doctors) ?></strong> doctor<?= count($doctors)!==1?'s':'' ?></div>
  <?php endif; ?>
</div>
<?php require_once '../includes/footer.php'; ?>
