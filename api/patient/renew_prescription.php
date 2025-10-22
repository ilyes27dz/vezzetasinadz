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

// احصل على معرّف المستخدم للطبيب
$stmt = $pdo->prepare("SELECT user_id FROM doctors WHERE id=?");
$stmt->execute([$pres['doctor_id']]);
$doctor_user_id = $stmt->fetchColumn();

if (!$doctor_user_id) {
    $_SESSION['msg'] = "حدث خطأ في جلب بيانات الطبيب.";
    header("Location: prescription.php"); exit;
}

// إرسال طلب للطبيب
$pdo->prepare("INSERT INTO prescription_renew_requests (prescription_id, patient_id, doctor_id) VALUES (?, ?, ?)")
    ->execute([$prescription_id, $_SESSION['user_id'], $doctor_user_id]);

// إشعار للطبيب (اختياري)
require_once '../functions/notifications.php';
add_notification($pdo, $doctor_user_id, 'prescription_renew', $prescription_id, "طلب تجديد وصفة طبية من مريض.");

$_SESSION['msg'] = "تم إرسال طلب التجديد للطبيب، سيتم إشعارك عند الموافقة.";
header("Location: prescription.php");
exit;
?>