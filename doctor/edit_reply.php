<?php
require_once '../db.php';
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'doctor' || !isset($_GET['id'])) {
    header("Location: ../auth/login.php");
    exit;
}
$id = intval($_GET['id']);
$stmt = $pdo->prepare("SELECT * FROM consultations WHERE id = ?");
$stmt->execute([$id]);
$consult = $stmt->fetch(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $reply = trim($_POST['reply']);
    $pdo->prepare("UPDATE consultations SET reply = ?, reply_date = NOW() WHERE id = ?")->execute([$reply, $id]);
    header("Location: consultations.php");
    exit;
}
?>
<?php include '../includes/header.php'; ?>
<div class="container py-5">
    <a href="consultations.php" class="btn btn-light mb-3"><i class="bi bi-arrow-right"></i> رجوع للاستشارات</a>
    <h3 class="mb-4 text-success">تعديل الرد</h3>
    <form method="post" class="w-50 mx-auto">
        <label>نص الرد الجديد:</label>
        <textarea name="reply" class="form-control mb-3" required><?= htmlspecialchars($consult['reply']) ?></textarea>
        <button type="submit" class="btn btn-success">حفظ التعديل</button>
    </form>
</div>
<?php include '../includes/footer.php'; ?>