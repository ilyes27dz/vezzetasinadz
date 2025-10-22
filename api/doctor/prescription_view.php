<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'doctor') {
    header("Location: ../auth/login.php");
    exit;
}
require_once '../db.php';

$id = intval($_GET['id'] ?? 0);

$stmt = $pdo->prepare("SELECT p.*, u.name AS patient_name FROM prescriptions p JOIN users u ON p.patient_id = u.id WHERE p.id = ?");
$stmt->execute([$id]);
$pres = $stmt->fetch();

if (!$pres) {
    echo "<div class='alert alert-danger'>الوصفة غير موجودة!</div>";
    exit;
}
?>
<?php include '../includes/header.php'; ?>
<div class="container py-4">
    <a href="prescriptions.php" class="btn btn-light mb-3"><i class="bi bi-arrow-right"></i> رجوع</a>
    <div class="card shadow p-4 position-relative">
        <?php if ($pres['auto_renew']): ?>
            <div class="position-absolute top-0 start-50 translate-middle-x" style="z-index:10;">
                <span class="badge bg-info text-dark p-3 fs-6 mt-2 mb-3 shadow">
                    <i class="bi bi-alarm"></i>
                    <b>التجديد التلقائي مفعّل</b> — هذه الوصفة مجدولة للتجديد التلقائي كل 3 أشهر.
                </span>
            </div>
            <div style="margin-bottom:3.5rem"></div>
        <?php endif; ?>
        <h3 class="text-success mb-3 mt-4"><i class="bi bi-file-earmark-medical"></i> تفاصيل الوصفة الطبية</h3>
        <ul class="list-group list-group-flush">
            <li class="list-group-item"><b>المريض:</b> <?= htmlspecialchars($pres['patient_name']) ?></li>
            <li class="list-group-item"><b>تاريخ الإنشاء:</b> <?= htmlspecialchars($pres['created_at']) ?></li>
            <li class="list-group-item"><b>الوصفة:</b> <br><?= nl2br(htmlspecialchars($pres['notes'])) ?></li>
            <li class="list-group-item"><b>ملاحظات الطبيب:</b> <br><?= nl2br(htmlspecialchars($pres['doctor_notes'])) ?></li>
            <li class="list-group-item">
                <b>ملف مرفق:</b>
                <?php if ($pres['file']): ?>
                    <a href="../uploads/<?= htmlspecialchars($pres['file']) ?>" target="_blank" class="btn btn-outline-primary btn-sm">تحميل الملف</a>
                <?php else: ?>
                    <span class="text-muted">لا يوجد</span>
                <?php endif; ?>
            </li>
            <li class="list-group-item">
                <b>التجديد التلقائي:</b>
                <?php if ($pres['auto_renew']): ?>
                    <span class="badge bg-success">مفعّل</span>
                    <span class="text-muted ms-2">سيتم إرسال طلب تلقائي للطبيب كل 3 أشهر</span>
                <?php else: ?>
                    <span class="badge bg-secondary">غير مفعّل</span>
                <?php endif; ?>
            </li>
        </ul>
    </div>
</div>
<?php include '../includes/footer.php'; ?>