<?php
session_start();
require_once '../includes/db.php';
$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare("DELETE FROM patreg WHERE id = ?");
$stmt->execute([$id]);
$_SESSION['flash'] = $stmt->rowCount()
    ? ['type'=>'success','msg'=>'Patient deleted successfully.']
    : ['type'=>'danger', 'msg'=>'Patient not found.'];
header('Location: /hospital/patients/index.php'); exit;
