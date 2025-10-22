<?php
require_once '../db.php';
session_start();
if(!isset($_SESSION['user_id']) || $_SESSION['role']!=='doctor' || !isset($_GET['id'])){header("Location: ../auth/login.php");exit;}
$patient_id = intval($_GET['id']);
$stmt = $pdo->prepare("SELECT name FROM users WHERE id = ?");
$stmt->execute([$patient_id]);
$patient_name = $stmt->fetchColumn();

// جلب معرف الدكتور من جدول doctors
$doctor_id_stmt = $pdo->prepare("SELECT id FROM doctors WHERE user_id = ?");
$doctor_id_stmt->execute([$_SESSION['user_id']]);
$doctor_id = $doctor_id_stmt->fetchColumn();

$user_id = $_SESSION['user_id']; // معرف المستخدم للطبيب

// إرسال رسالة جديدة
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['message'])) {
    require_once '../functions/notifications.php';
    $msg = trim($_POST['message']);
    $stmt = $pdo->prepare("INSERT INTO messages (doctor_id, patient_id, message, sender_id, receiver_id, sent_at) VALUES (?, ?, ?, ?, ?, NOW())");
    $stmt->execute([$doctor_id, $patient_id, $msg, $user_id, $patient_id]);
    $message_id = $pdo->lastInsertId();

    add_notification($pdo, $patient_id, 'doctor_message', $message_id, "رسالة جديدة من الطبيب.");
    header("Location: message_patient.php?id=".$patient_id);
    exit;
}

// جلب جميع الرسائل
$stmt = $pdo->prepare("SELECT * FROM messages WHERE doctor_id = ? AND patient_id = ? ORDER BY sent_at DESC");
$stmt->execute([$doctor_id, $patient_id]);
$messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<?php include '../includes/header.php'; ?>
<div class="container py-5">
    <a href="patients.php" class="btn btn-light mb-3"><i class="bi bi-arrow-right"></i> رجوع لملفات المرضى</a>
    <h3 class="mb-4 text-info"><i class="bi bi-chat-dots"></i> تواصل مع <?= htmlspecialchars($patient_name) ?></h3>
    <form method="post" class="mb-3 w-75 mx-auto border p-3 rounded-3 bg-light shadow-sm">
        <textarea name="message" class="form-control mb-2" rows="3" placeholder="اكتب رسالتك للمريض ..." required></textarea>
        <button type="submit" class="btn btn-info w-100">إرسال</button>
    </form>
    <div class="w-75 mx-auto">
        <h5 class="text-secondary mb-3"><i class="bi bi-envelope-paper"></i> سجل الرسائل</h5>
        <?php foreach($messages as $msg): ?>
            <div class="alert alert-primary mb-2 py-2 d-flex justify-content-between">
                <div><?= nl2br(htmlspecialchars($msg['message'])) ?></div>
                <small class="text-muted"><?= date('Y-m-d H:i', strtotime($msg['sent_at'])) ?></small>
            </div>
        <?php endforeach; ?>
        <?php if(empty($messages)): ?>
            <div class="alert alert-light text-center">لا توجد رسائل سابقة.</div>
        <?php endif; ?>
    </div>
</div>
<?php include '../includes/footer.php'; ?>