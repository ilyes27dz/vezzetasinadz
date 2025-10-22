<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();
header('Content-Type: application/json');
require_once '../db.php';

$pharmacy_id = intval($_POST['pharmacy_id']);
$drug_name = trim($_POST['drug_name']);
$delivery = isset($_POST['delivery']) ? $_POST['delivery'] : '';
$notes = isset($_POST['notes']) ? trim($_POST['notes']) : '';

if(isset($_SESSION['user_id']) && $_SESSION['role'] == 'patient') {
    $patient_id = $_SESSION['user_id'];
    $stmt = $pdo->prepare("SELECT email, phone FROM users WHERE id=?");
    $stmt->execute([$patient_id]);
    $userData = $stmt->fetch();
    $email = $userData ? $userData['email'] : '';
    $phone = $userData ? $userData['phone'] : '';
} else {
    $patient_id = null;
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';
}

if (!$pharmacy_id || !$drug_name) {
    echo json_encode(['success'=>false, 'message'=>"يرجى اختيار الصيدلية واسم الدواء"]);
    exit;
}
if (!$patient_id && (empty($email) || empty($phone))) {
    echo json_encode(['success'=>false, 'message'=>"يرجى إدخال البريد الإلكتروني ورقم الهاتف"]);
    exit;
}

$stmt = $pdo->prepare("SELECT price FROM pharmacy_drugs WHERE pharmacy_id=? AND name=? AND is_available=1");
$stmt->execute([$pharmacy_id, $drug_name]);
$price = $stmt->fetchColumn();
$price = $price ? intval($price) : 0;
$delivery_fee = ($delivery == 'delivery') ? 300 : 0;
$total_price = $price + $delivery_fee;

if ($price == 0) {
    echo json_encode(['success'=>false, 'message'=>"يرجى اختيار دواء متوفر من قائمة الصيدلية"]);
    exit;
}

$stmt = $pdo->prepare("INSERT INTO drug_orders (
    patient_id, pharmacy_id, drug_name, price, notes, delivery, email, phone, created_at
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())");
$stmt->execute([
    $patient_id,
    $pharmacy_id,
    $drug_name,
    $total_price,
    $notes,
    $delivery,
    $email,
    $phone
]);

// إشعار الصيدلية
$notif_msg = "وصل طلب جديد لدواء ($drug_name) من أحد المرضى أو الزوار.";
$notif_type = "order_drug";
$stmt = $pdo->prepare("SELECT id FROM users WHERE role='pharmacy' AND pharmacy_id=? LIMIT 1");
$stmt->execute([$pharmacy_id]);
$pharmacy_user_id = $stmt->fetchColumn();
if ($pharmacy_user_id) {
    $stmt = $pdo->prepare("INSERT INTO notifications (user_id, message, type, created_at) VALUES (?, ?, ?, NOW())");
    $stmt->execute([$pharmacy_user_id, $notif_msg, $notif_type]);
}
echo json_encode(['success'=>true, 'message'=>"تم إرسال طلب الدواء بنجاح! السعر النهائي: <b>$total_price دج</b>"]);