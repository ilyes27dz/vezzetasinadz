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

$pdo->prepare("UPDATE prescriptions SET auto_renew=1 WHERE id=?")->execute([$prescription_id]);

// إشعار للطبيب عند تفعيل الخدمة
$stmt = $pdo->prepare("SELECT user_id FROM doctors WHERE id=?");
$stmt->execute([$pres['doctor_id']]);
$doctor_user_id = $stmt->fetchColumn();
if ($doctor_user_id) {
    require_once '../functions/notifications.php';
    add_notification(
        $pdo,
        $doctor_user_id,
        'auto_renew_enabled',
        $prescription_id,
        "قام المريض بتفعيل خدمة التجديد التلقائي للوصفة الطبية رقم #$prescription_id."
    );
}

$_SESSION['msg'] = "تم تفعيل التجديد التلقائي، سيتم إرسال طلب للطبيب كل 3 أشهر.";
header("Location: prescription.php");
exit;
?>