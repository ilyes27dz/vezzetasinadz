<?php
session_start();
if(!isset($_SESSION['user_id']) || $_SESSION['role']!=='doctor'){header("Location: ../auth/login.php");exit;}
require_once '../db.php';

// حذف مريض عند الضغط على زر الحذف
if (isset($_GET['delete'])) {
    $patient_id = intval($_GET['delete']);
    $pdo->prepare("DELETE FROM consultations WHERE patient_id = ?")->execute([$patient_id]);
    $pdo->prepare("DELETE FROM prescriptions WHERE patient_id = ?")->execute([$patient_id]);
    $pdo->prepare("DELETE FROM messages WHERE patient_id = ?")->execute([$patient_id]);
    // إذا أردت حذف سجل المستخدم نفسه أزل التعليق عن السطر التالي
    // $pdo->prepare("DELETE FROM users WHERE id = ?")->execute([$patient_id]);
    header("Location: patients.php");
    exit;
}

// جلب كل المرضى (يمكنك تعديل الشرط حسب الحاجة)
$stmt = $pdo->prepare("SELECT id, name, email FROM users WHERE role = 'patient'");
$stmt->execute();
$patients = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<?php include '../includes/header.php'; ?>
<div class="container py-5">
    <h3 class="mb-4 text-success"><i class="bi bi-person-lines-fill"></i> ملفات المرضى</h3>
    <div class="row g-4">
    <?php foreach($patients as $p): ?>
        <div class="col-md-4">
            <div class="card shadow h-100 border-0 bg-white">
                <div class="card-body d-flex flex-column align-items-center text-center">
                    <i class="bi bi-person-circle" style="font-size:4rem;color:#388e3c;"></i>
                    <h5 class="card-title text-primary mb-2"><?= htmlspecialchars($p['name']) ?></h5>
                    <p class="card-text mb-2"><i class="bi bi-envelope"></i> <?= htmlspecialchars($p['email']) ?></p>
                    <a href="patient_file.php?id=<?= $p['id'] ?>" class="btn btn-outline-info btn-sm w-75 mb-2">
                        <i class="bi bi-folder2-open"></i> عرض الملف الطبي
                    </a>
                    <a href="edit_patient.php?id=<?= $p['id'] ?>" class="btn btn-secondary btn-sm w-75 mb-2">
                        <i class="bi bi-pencil"></i> تعديل
                    </a>
                    <a href="patients.php?delete=<?= $p['id'] ?>" class="btn btn-danger btn-sm w-75 mb-2"
                        onclick="return confirm('هل أنت متأكد أنك تريد حذف هذا المريض وكل ملفاته؟');">
                        <i class="bi bi-trash"></i> حذف
                    </a>
                    <a href="message_patient.php?id=<?= $p['id'] ?>" class="btn btn-info btn-sm w-75">
                        <i class="bi bi-chat-dots"></i> تواصل
                    </a>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
    <?php if (empty($patients)): ?>
        <div class="col-12"><div class="alert alert-info text-center">لا يوجد مرضى.</div></div>
    <?php endif; ?>
    </div>
</div>
<?php include '../includes/footer.php'; ?>