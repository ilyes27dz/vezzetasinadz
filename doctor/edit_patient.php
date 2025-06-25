<?php
require_once '../db.php';
session_start();
if(!isset($_SESSION['user_id']) || $_SESSION['role']!=='doctor' || !isset($_GET['id'])){header("Location: ../auth/login.php");exit;}
$patient_id = intval($_GET['id']);
$stmt = $pdo->prepare("SELECT name, email FROM users WHERE id = ?");
$stmt->execute([$patient_id]);
$patient = $stmt->fetch(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $stmt = $pdo->prepare("UPDATE users SET name = ?, email = ? WHERE id = ?");
    $stmt->execute([$name, $email, $patient_id]);
    header("Location: patients.php");
    exit;
}
?>
<?php include '../includes/header.php'; ?>
<div class="container py-5">
    <a href="patients.php" class="btn btn-light mb-3"><i class="bi bi-arrow-right"></i> رجوع لملفات المرضى</a>
    <h3 class="mb-4 text-success"><i class="bi bi-pencil"></i> تعديل بيانات المريض</h3>
    <form method="post" class="w-50 mx-auto border p-4 shadow-lg rounded-3 bg-light">
        <label class="mb-2 fw-bold">الاسم:</label>
        <input type="text" name="name" class="form-control mb-3" value="<?= htmlspecialchars($patient['name']) ?>" required>
        <label class="mb-2 fw-bold">البريد الإلكتروني:</label>
        <input type="email" name="email" class="form-control mb-3" value="<?= htmlspecialchars($patient['email']) ?>" required>
        <button type="submit" class="btn btn-success w-100">حفظ التعديل</button>
    </form>
</div>
<?php include '../includes/footer.php'; ?>