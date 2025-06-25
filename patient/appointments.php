<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'patient') {
    header("Location: ../auth/login.php"); exit;
}
require_once '../db.php';

$stmt = $pdo->prepare("SELECT a.*, u.name AS doctor_name 
    FROM appointments a 
    JOIN doctors d ON a.doctor_id = d.id 
    JOIN users u ON d.user_id = u.id 
    WHERE a.patient_id = ?
    ORDER BY a.date DESC");
$stmt->execute([$_SESSION['user_id']]);
$appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);

function get_status_badge($status) {
    $status = strtolower($status);
    if ($status == 'confirmed') {
        return '<span class="badge status-badge confirmed"><i class="bi bi-check-circle"></i> مؤكد</span>';
    } elseif ($status == 'pending') {
        return '<span class="badge status-badge pending"><i class="bi bi-clock-history"></i> قيد الدراسة</span>';
    } elseif ($status == 'cancelled' || $status == 'canceled') {
        return '<span class="badge status-badge cancelled"><i class="bi bi-x-circle"></i> ملغى</span>';
    } elseif ($status == 'completed') {
        return '<span class="badge status-badge completed"><i class="bi bi-check2-square"></i> مكتمل</span>';
    }
    return '<span class="badge status-badge other">'.htmlspecialchars($status).'</span>';
}
?>
<?php include '../includes/header.php'; ?>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
<style>
.page-title {
    color: #009c86;
    font-weight: 800;
    letter-spacing: 1px;
    margin-bottom: 28px;
}
.status-badge { font-size: 1.01rem; font-weight: 700; padding: 6px 18px; border-radius: 15px; vertical-align: middle; }
.status-badge.confirmed { background: #e8f5e9; color: #218741; border: 1.5px solid #388e3c;}
.status-badge.pending { background: #fffde7; color: #c77d00; border: 1.5px solid #ffe082;}
.status-badge.cancelled { background: #ffebee; color: #e53935; border: 1.5px solid #e57373;}
.status-badge.completed { background: #e3f2fd; color: #0277bd; border: 1.5px solid #64b5f6;}
.status-badge.other { background: #f3e5f5; color: #6a1b9a; border: 1.5px solid #ba68c8;}
.table-appointments {
    background: #fff;
    border-radius: 18px;
    box-shadow: 0 4px 22px #27e6a115;
    overflow: hidden;
    margin: 38px 0 0 0;
}
.table thead tr th {
    background: #e0f7fa;
    color: #009688;
    font-weight: bold;
    font-size: 1.08rem;
}
.table-appointments .table-striped>tbody>tr:nth-of-type(odd) {
    background-color: #f8fff6;
}
.table-appointments .table-striped>tbody>tr:nth-of-type(even) {
    background-color: #eafaf3;
}
.table-appointments td, .table-appointments th {
    vertical-align: middle;
    text-align: center;
}
@media (max-width: 700px) {
    .table-appointments { font-size: 0.97rem; }
    .table-appointments th, .table-appointments td { padding: 6px 2px !important;}
}
</style>

<div class="container py-4">
    <h2 class="page-title"><i class="bi bi-calendar2-week"></i> جميع مواعيدي الطبية</h2>
    <div class="table-responsive table-appointments">
        <table class="table table-striped align-middle mb-0">
            <thead>
                <tr>
                    <th>التاريخ</th>
                    <th>الوقت</th>
                    <th>اسم الطبيب</th>
                    <th>الحالة</th>
                    <th>طباعة</th>
                </tr>
            </thead>
            <tbody>
                <?php if(empty($appointments)): ?>
                    <tr>
                        <td colspan="5" class="text-center text-muted">لا توجد مواعيد محفوظة في حسابك بعد.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach($appointments as $a): ?>
                        <tr>
                            <td>
                                <span class="date-badge">
                                    <i class="bi bi-calendar-event"></i>
                                    <?=htmlspecialchars($a['date'])?>
                                </span>
                            </td>
                            <td>
                                <?php
                                    $appt_time = '';
                                    if (!empty($a['appointment_date'])) $appt_time = date('H:i', strtotime($a['appointment_date']));
                                    elseif (!empty($a['time'])) $appt_time = htmlspecialchars($a['time']);
                                ?>
                                <span class="time-badge">
                                    <i class="bi bi-clock"></i> <?=$appt_time?>
                                </span>
                            </td>
                            <td>
                                <span class="doctor-name">
                                    <i class="bi bi-person-badge"></i>
                                    د. <?=htmlspecialchars($a['doctor_name'])?>
                                </span>
                            </td>
                            <td>
                                <?=get_status_badge($a['status'])?>
                            </td>
                            <td>
                                <a href="print_appointment.php?id=<?=$a['id']?>" class="btn btn-outline-success btn-sm"><i class="bi bi-printer"></i> طباعة</a>
                            </td>
                        </tr>
                    <?php endforeach;?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php include '../includes/footer.php'; ?>