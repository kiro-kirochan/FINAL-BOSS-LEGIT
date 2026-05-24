<?php
session_start();
require_once '../includes/db.php';
$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare("DELETE FROM appointmenttb WHERE id = ?");
$stmt->execute([$id]);
$_SESSION['flash'] = $stmt->rowCount()
    ? ['type'=>'success','msg'=>'Appointment deleted successfully.']
    : ['type'=>'danger', 'msg'=>'Appointment not found.'];
header('Location: /hospital/appointments/index.php'); exit;
