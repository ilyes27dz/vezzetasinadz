<?php
require_once '../db.php';
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'doctor' || !isset($_GET['id'])) {
    header("Location: ../auth/login.php");
    exit;
}
$id = intval($_GET['id']);

// أولاً احذف جميع الطلبات المرتبطة بهذه الوصفة
$pdo->prepare("DELETE FROM prescription_renew_requests WHERE prescription_id = ?")->execute([$id]);

// ثم احذف الوصفة نفسها
$pdo->prepare("DELETE FROM prescriptions WHERE id = ?")->execute([$id]);

header("Location: prescriptions.php");
exit;
?>