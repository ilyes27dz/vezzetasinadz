<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'patient') {
    header("Location: ../auth/login.php"); exit;
}
require_once '../db.php';
$stmt = $pdo->prepare("SELECT m.*, u.name AS doctor_name FROM messages m 
    JOIN users u ON m.sender_id=u.id 
    WHERE m.receiver_id=? AND m.sender_id!=? 
    ORDER BY m.sent_at DESC");
$stmt->execute([$_SESSION['user_id'], $_SESSION['user_id']]);
$doctor_messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<?php include '../includes/header.php'; ?>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
<style>
.page-title { color: #007b83; font-weight: bold; margin-bottom: 30px; }
.message-card {
    background: linear-gradient(100deg, #f9fcfa 75%, #e8f5e9 100%);
    border-radius: 18px;
    box-shadow: 0 2px 14px #b6e7c7;
    margin-bottom: 18px;
    border: none;
    padding: 18px 18px 12px 18px;
    position: relative;
}
.message-card .doctor-name { color: #388e3c; font-weight: bold; font-size: 1.04rem;}
.message-card .date-badge {
    background: #36c66f; color: #fff; padding: 5px 13px; border-radius: 12px; font-weight: 700; font-size: 1.01rem;
    margin-left: 7px;
}
.message-text {
    background: #e3f2fd;
    border-radius: 12px;
    padding: 13px 17px;
    margin: 10px 0 0 0;
    color: #333;
    font-size: 1.06rem;
}
@media (max-width: 700px){
    .message-card { padding: 10px 4px 8px 4px;}
}
</style>
<div class="container py-4">
    <h2 class="page-title"><i class="bi bi-envelope-paper"></i> رسائل الطبيب</h2>
    <?php if (empty($doctor_messages)): ?>
        <div class="alert alert-info text-center">لا توجد رسائل مرسلة من الطبيب.</div>
    <?php else: ?>
        <?php foreach($doctor_messages as $msg): ?>
            <div class="message-card">
                <div class="d-flex align-items-center mb-2" style="gap:10px;">
                    <span class="date-badge"><i class="bi bi-clock"></i> <?=htmlspecialchars($msg['sent_at'])?></span>
                    <span class="doctor-name"><i class="bi bi-person-badge"></i> د. <?=htmlspecialchars($msg['doctor_name'])?></span>
                </div>
                <div class="message-text"><?=nl2br(htmlspecialchars($msg['message']))?></div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
<?php include '../includes/footer.php'; ?>