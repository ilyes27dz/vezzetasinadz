<?php
require_once '../db.php';
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'doctor' || !isset($_GET['id'])) {
    header("Location: ../auth/login.php");
    exit;
}
$id = intval($_GET['id']);
$stmt = $pdo->prepare("SELECT * FROM appointments WHERE id = ?");
$stmt->execute([$id]);
$app = $stmt->fetch(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_date = date('Y-m-d H:i:00', strtotime($_POST['appointment_date']));
    $stmt = $pdo->prepare("UPDATE appointments SET appointment_date = ? WHERE id = ?");
    $stmt->execute([$new_date, $id]);
    header("Location: appointments.php");
    exit;
}
?>
<?php include '../includes/header.php'; ?>
<div class="container py-5">
    <a href="appointments.php" class="btn btn-light mb-3"><i class="bi bi-arrow-right"></i> رجوع للمواعيد</a>
    <h3 class="mb-4 text-success"><i class="bi bi-pencil-square"></i> تعديل موعد</h3>
    <form method="post" class="w-50 mx-auto border p-4 shadow-lg rounded-3 bg-light">
        <label class="mb-2 fw-bold">تاريخ ووقت الموعد الجديد:</label>
        <input type="datetime-local" name="appointment_date" class="form-control mb-3"
               value="<?= date('Y-m-d\TH:i', strtotime($app['appointment_date'])) ?>" required>
        <button type="submit" class="btn btn-success w-100">حفظ التعديل</button>
    </form>
</div>
<?php include '../includes/footer.php'; ?>