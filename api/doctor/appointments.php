<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'doctor') {
    header("Location: ../auth/login.php");
    exit;
}
require_once '../db.php';

$stmt = $pdo->prepare("SELECT id FROM doctors WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$doctor_id = $stmt->fetchColumn();

// ترجمة الحالة
$status_labels = [
    'pending' => 'قيد الدراسة',
    'confirmed' => 'مؤكد',
    'completed' => 'مكتمل',
    'cancelled' => 'ملغى'
];
$status_color = [
    'pending' => 'warning',
    'confirmed' => 'success',
    'completed' => 'info',
    'cancelled' => 'danger'
];

// تنفيذ الأوامر
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'], $_POST['action'])) {
    require_once '../functions/notifications.php';
    $id = intval($_POST['id']);
    $action = $_POST['action'];
    $apptStmt = $pdo->prepare("SELECT * FROM appointments WHERE id = ?");
    $apptStmt->execute([$id]);
    $appointment = $apptStmt->fetch(PDO::FETCH_ASSOC);

    if ($appointment) {
        $patient_id = $appointment['patient_id'];
        if ($action == 'confirm') {
            $pdo->prepare("UPDATE appointments SET status = 'confirmed' WHERE id = ?")->execute([$id]);
            add_notification($pdo, $patient_id, 'appointment_status', $id, "تم تأكيد موعدك من الطبيب.");

            // تحديث عدد الحجوزات للطبيب عند التأكيد
            $subscription = $pdo->prepare("SELECT id FROM subscriptions WHERE user_id=? AND status='active' ORDER BY id DESC LIMIT 1");
            $subscription->execute([$_SESSION['user_id']]);
            $sub = $subscription->fetch(PDO::FETCH_ASSOC);
            if ($sub) {
                $pdo->prepare("UPDATE subscriptions SET used_appointments = used_appointments + 1 WHERE id = ?")
                    ->execute([$sub['id']]);
            }
        } elseif ($action == 'cancel') {
            $pdo->prepare("UPDATE appointments SET status = 'cancelled' WHERE id = ?")->execute([$id]);
            add_notification($pdo, $patient_id, 'appointment_status', $id, "تم إلغاء موعدك من الطبيب.");
        } elseif ($action == 'pending') {
            $pdo->prepare("UPDATE appointments SET status = 'pending' WHERE id = ?")->execute([$id]);
            add_notification($pdo, $patient_id, 'appointment_status', $id, "تمت إعادة موعدك إلى قيد الدراسة من الطبيب.");
        } elseif ($action == 'reschedule' && !empty($_POST['new_date'])) {
            $new_date = date('Y-m-d H:i:00', strtotime($_POST['new_date']));
            $pdo->prepare("UPDATE appointments SET status = 'confirmed', appointment_date = ? WHERE id = ?")
                ->execute([$new_date, $id]);
            add_notification($pdo, $patient_id, 'appointment_status', $id, "تم تعديل موعدك من الطبيب.");

            // تحديث عدد الحجوزات للطبيب عند التأجيل (يحسبها كـ"تأكيد")
            $subscription = $pdo->prepare("SELECT id FROM subscriptions WHERE user_id=? AND status='active' ORDER BY id DESC LIMIT 1");
            $subscription->execute([$_SESSION['user_id']]);
            $sub = $subscription->fetch(PDO::FETCH_ASSOC);
            if ($sub) {
                $pdo->prepare("UPDATE subscriptions SET used_appointments = used_appointments + 1 WHERE id = ?")
                    ->execute([$sub['id']]);
            }
        }
    }
    header("Location: appointments.php");
    exit;
}

// حذف الموعد
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $pdo->prepare("DELETE FROM appointments WHERE id = ?")->execute([$id]);
    header("Location: appointments.php");
    exit;
}

// جلب المواعيد
$stmt = $pdo->prepare("SELECT a.*, u.name AS patient_name, u.email AS patient_email 
    FROM appointments a 
    JOIN users u ON a.patient_id = u.id 
    WHERE a.doctor_id = ? 
    ORDER BY a.appointment_date DESC");
$stmt->execute([$doctor_id]);
$appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<?php include '../includes/header.php'; ?>
<div class="container py-5">
    <a href="index.php" class="btn btn-light mb-3"><i class="bi bi-arrow-right"></i> رجوع للواجهة</a>
    <h3 class="mb-4 text-success"><i class="bi bi-calendar2-week"></i> إدارة المواعيد</h3>
    <div class="table-responsive">
    <table class="table table-hover table-bordered shadow-sm align-middle bg-white">
        <thead class="table-success">
            <tr class="text-center">
                <th>المريض</th>
                <th>البريد الإلكتروني</th>
                <th>التاريخ والوقت</th>
                <th>الحالة</th>
                <th>إجراءات</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach($appointments as $a): ?>
            <tr class="text-center">
                <td class="fw-bold"><?= htmlspecialchars($a['patient_name']) ?></td>
                <td><?= htmlspecialchars($a['patient_email']) ?></td>
                <td>
                    <?php
                    $dateValue = !empty($a['appointment_date']) ? $a['appointment_date'] : $a['date'];
                    if(!empty($dateValue) && strtotime($dateValue)) {
                        echo '<span class="badge bg-info text-dark">';
                        echo date('l d/m/Y', strtotime($dateValue));
                        echo '<br><i class="bi bi-clock"></i> ';
                        echo date('H:i', strtotime($dateValue));
                        echo '</span>';
                    } else {
                        echo '<span class="text-danger">تاريخ غير محدد</span>';
                    }
                    ?>
                </td>
                <td>
                    <span class="badge bg-<?= $status_color[$a['status']] ?>">
                        <?= $status_labels[$a['status']] ?? htmlspecialchars($a['status']) ?>
                    </span>
                </td>
                <td>
                    <div class="d-flex flex-wrap gap-1 justify-content-center">
                        <!-- تأكيد -->
                        <form method="post" style="display:inline;">
                            <input type="hidden" name="id" value="<?= $a['id'] ?>">
                            <button name="action" value="confirm" class="btn btn-success btn-sm" <?= ($a['status']=='confirmed')?'disabled':''; ?>>
                                <i class="bi bi-check-circle"></i> تأكيد
                            </button>
                        </form>
                        <!-- رفض -->
                        <form method="post" style="display:inline;">
                            <input type="hidden" name="id" value="<?= $a['id'] ?>">
                            <button name="action" value="cancel" class="btn btn-danger btn-sm" <?= ($a['status']=='cancelled')?'disabled':''; ?>>
                                <i class="bi bi-x-circle"></i> إلغاء
                            </button>
                        </form>
                        <!-- تأجيل -->
                        <form method="post" style="display:inline;max-width:170px;">
                            <input type="hidden" name="id" value="<?= $a['id'] ?>">
                            <input type="datetime-local" name="new_date" class="form-control form-control-sm mb-1" style="min-width:130px;">
                            <button name="action" value="reschedule" class="btn btn-warning btn-sm w-100">
                                <i class="bi bi-arrow-repeat"></i> تأجيل
                            </button>
                        </form>
                        <!-- إرجاع للدراسة -->
                        <form method="post" style="display:inline;">
                            <input type="hidden" name="id" value="<?= $a['id'] ?>">
                            <button name="action" value="pending" class="btn btn-secondary btn-sm" <?= ($a['status']=='pending')?'disabled':''; ?>>
                                <i class="bi bi-arrow-counterclockwise"></i> قيد الدراسة
                            </button>
                        </form>
                        <!-- حذف -->
                        <a href="appointments.php?delete=<?= $a['id'] ?>" class="btn btn-danger btn-sm"
                           onclick="return confirm('هل أنت متأكد من حذف الموعد؟');">
                            <i class="bi bi-trash"></i>
                        </a>
                        <!-- طباعة -->
                        <a href="print_appointment.php?id=<?= $a['id'] ?>" class="btn btn-primary btn-sm" target="_blank">
                            <i class="bi bi-printer"></i> طباعة
                        </a>
                        <!-- تعديل -->
                        <a href="edit_appointment.php?id=<?= $a['id'] ?>" class="btn btn-info btn-sm">
                            <i class="bi bi-pencil-square"></i> تعديل
                        </a>
                    </div>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php if (empty($appointments)): ?>
            <tr><td colspan="5" class="text-center">لا توجد مواعيد.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
    </div>
</div>
<?php include '../includes/footer.php'; ?>