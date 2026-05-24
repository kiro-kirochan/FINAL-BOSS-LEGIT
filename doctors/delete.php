<?php
session_start();
require_once '../includes/db.php';
$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare("DELETE FROM doctb WHERE id = ?");
$stmt->execute([$id]);
$_SESSION['flash'] = $stmt->rowCount()
    ? ['type'=>'success','msg'=>'Doctor deleted successfully.']
    : ['type'=>'danger', 'msg'=>'Doctor not found.'];
header('Location: /hospital/doctors/index.php'); exit;
