<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'doctor') {
    header("Location: ../auth/login.php");
    exit;
}
require_once '../db.php';

// جلب doctor_id
$stmt = $pdo->prepare("SELECT id FROM doctors WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$doctor_id = $stmt->fetchColumn();
if (!$doctor_id) { die("لم يتم العثور على بيانات الطبيب."); }

// جلب الاستشارات الخاصة بالطبيب مع اسم المريض
$stmt = $pdo->prepare(
    "SELECT c.*, u.name AS patient_name
     FROM consultations c
     JOIN users u ON c.patient_id = u.id
     WHERE c.doctor_id = ?
     ORDER BY c.created_at DESC"
);
$stmt->execute([$doctor_id]);
$consultations = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<?php include '../includes/header.php'; ?>
<div class="container py-5">
    <h3 class="mb-4 text-success">استشارات ورسائل المرضى</h3>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>المريض</th>
                <th>نص الاستشارة</th>
                <th>تاريخ الإرسال</th>
                <th>رد الطبيب</th>
                <th>تاريخ الرد</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach($consultations as $c): ?>
            <tr>
                <td><?= htmlspecialchars($c['patient_name']) ?></td>
                <td><?= nl2br(htmlspecialchars($c['message'])) ?></td>
                <td><?= htmlspecialchars($c['created_at']) ?></td>
                <td><?= $c['reply'] ? nl2br(htmlspecialchars($c['reply'])) : '<span class="text-muted">لا يوجد رد</span>' ?></td>
                <td><?= $c['reply_date'] ? htmlspecialchars($c['reply_date']) : '-' ?></td>
            </tr>
        <?php endforeach; ?>
        <?php if (empty($consultations)): ?>
            <tr><td colspan="5" class="text-center">لا توجد استشارات.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>
<?php include '../includes/footer.php'; ?>