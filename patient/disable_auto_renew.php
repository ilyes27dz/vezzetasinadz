<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'patient') exit('Access denied');
require_once '../db.php';

$prescription_id = intval($_POST['prescription_id'] ?? 0);

$stmt = $pdo->prepare("SELECT * FROM prescriptions WHERE id=? AND patient_id=?");
$stmt->execute([$prescription_id, $_SESSION['user_id']]);
$pres = $stmt->fetch();

if (!$pres) {
    $_SESSION['msg'] = "لم يتم العثور على الوصفة.";
    header("Location: prescription.php"); exit;
}
$pdo->prepare("UPDATE prescriptions SET auto_renew=0 WHERE id=?")->execute([$prescription_id]);

$_SESSION['msg'] = "تم إيقاف التجديد التلقائي.";
header("Location: prescription.php");
exit;
?>