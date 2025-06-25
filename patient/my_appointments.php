<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'patient') { header("Location: ../auth/login.php"); exit; }
require_once '../db.php';
$apps = $pdo->prepare("SELECT a.*, u.name as doctor_name FROM appointments a JOIN doctors d ON a.doctor_id=d.id JOIN users u ON d.user_id=u.id WHERE a.patient_id=? ORDER BY a.date DESC");
$apps->execute([$_SESSION['user_id']]);
$apps = $apps->fetchAll();
?>
<?php include '../includes/header.php'; ?>
<div class="container py-5">
    <h3 class="mb-4 text-success">حجوزاتي</h3>
    <table class="table">
        <thead><tr><th>الطبيب</th><th>التاريخ</th><th>الساعة</th><th>الحالة</th><th>وصل الحجز</th></tr></thead>
        <tbody>
        <?php foreach($apps as $a): ?>
            <tr>
                <td><?=htmlspecialchars($a['doctor_name'])?></td>
                <td><?=htmlspecialchars($a['date'])?></td>
                <td><?=htmlspecialchars($a['time'])?></td>
                <td>
                    <?php
                    if($a['status']=='pending') echo '<span class="badge bg-warning">بانتظار الموافقة</span>';
                    elseif($a['status']=='approved') echo '<span class="badge bg-success">مقبول</span>';
                    elseif($a['status']=='modified') echo '<span class="badge bg-info">تم التعديل</span>';
                    ?>
                </td>
                <td>
                    <?php if($a['status']=='approved' || $a['status']=='modified'): ?>
                        <a href="print_appointment.php?id=<?=$a['id']?>" class="btn btn-outline-success btn-sm">طباعة الوصل</a>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php include '../includes/footer.php'; ?>