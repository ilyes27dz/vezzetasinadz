<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'pharmacy') {
    header("Location: ../auth/login.php"); exit;
}
require_once '../db.php';
$pharmacy_id = $_SESSION['pharmacy_id'];

// تغيير حالة الطلب
if (isset($_POST['rare_order_id'], $_POST['status'])) {
    $rare_order_id = intval($_POST['rare_order_id']);
    $status = $_POST['status'];
    $allowed = ['جديد','تم الاستقبال','جاري التحضير','تم الإرسال','تم الوصول','مرفوض'];
    if (in_array($status, $allowed)) {
        // تحديث الحالة في قاعدة البيانات
        $stmt = $pdo->prepare("UPDATE rare_drug_requests SET status=? WHERE id=? AND pharmacy_id=?");
        $stmt->execute([$status, $rare_order_id, $pharmacy_id]);

        // إشعار المريض (اختياري)
        $stmt = $pdo->prepare("SELECT patient_id FROM rare_drug_requests WHERE id=?");
        $stmt->execute([$rare_order_id]);
        $patient_id = $stmt->fetchColumn();
        if ($patient_id) {
            $notif_msg = "تم تحديث حالة طلب الدواء النادر الخاص بك إلى: $status";
            $notif_type = "rare_drug_status";
            $pdo->prepare("INSERT INTO notifications (user_id, message, type, created_at) VALUES (?, ?, ?, NOW())")
                ->execute([$patient_id, $notif_msg, $notif_type]);
        }
    }
}

// جلب كل الطلبات للأدوية النادرة التي تخص هذه الصيدلية
$stmt = $pdo->prepare(
    "SELECT r.*, d.name AS drug_name, d.category, u.name AS patient_name, u.email AS patient_email
     FROM rare_drug_requests r
     JOIN rare_drugs d ON r.drug_id = d.id
     JOIN users u ON r.patient_id = u.id
     WHERE r.pharmacy_id = ?
     ORDER BY r.requested_at DESC"
);
$stmt->execute([$pharmacy_id]);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

$statusStyles = [
    'جديد'         => ['class'=>'secondary','icon'=>'bi-clock'],
    'تم الاستقبال' => ['class'=>'info','icon'=>'bi-person-check'],
    'جاري التحضير' => ['class'=>'warning','icon'=>'bi-hourglass-split'],
    'تم الإرسال'   => ['class'=>'primary','icon'=>'bi-truck'],
    'تم الوصول'    => ['class'=>'success','icon'=>'bi-check-circle'],
    'مرفوض'        => ['class'=>'danger','icon'=>'bi-x-circle'],
];
?>
<?php include '../includes/header.php'; ?>
<div class="container py-5">
    <h3 class="mb-4 text-danger"><i class="bi bi-exclamation-circle"></i> طلبات الأدوية النادرة</h3>
    <div class="card p-3">
        <table class="table align-middle table-bordered table-hover text-center">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>اسم المريض</th>
                    <th>البريد</th>
                    <th>رقم الهاتف</th>
                    <th>اسم الدواء</th>
                    <th>الفئة</th>
                    <th>الوصفة الطبية</th>
                    <th>ملاحظات</th>
                    <th>تاريخ الطلب</th>
                    <th>الحالة</th>
                    <th>تغيير الحالة</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach($orders as $i => $order): 
                $style = $statusStyles[$order['status']] ?? ['class'=>'light','icon'=>'bi-question-circle'];
            ?>
                <tr>
                    <td><?= $i+1 ?></td>
                    <td><?= htmlspecialchars($order['patient_name']) ?></td>
                    <td><?= htmlspecialchars($order['patient_email']) ?></td>
                    <td><?= htmlspecialchars($order['phone']) ?></td>
                    <td><?= htmlspecialchars($order['drug_name']) ?></td>
                    <td><?= htmlspecialchars($order['category']) ?></td>
                    <td>
                        <?php if($order['prescription_file']): ?>
                            <a href="../rare_prescriptions/<?=htmlspecialchars($order['prescription_file'])?>" class="btn btn-sm btn-info" target="_blank">عرض</a>
                        <?php else: ?>
                            <span class="text-muted">لا توجد</span>
                        <?php endif; ?>
                    </td>
                    <td><?= htmlspecialchars($order['notes']) ?></td>
                    <td><?= htmlspecialchars($order['requested_at']) ?></td>
                    <td>
                        <span class="badge bg-<?=$style['class']?> px-3 py-2">
                            <i class="bi <?=$style['icon']?>"></i>
                            <?=$order['status']?>
                        </span>
                    </td>
                    <td>
                        <?php if($order['status'] != 'تم الوصول' && $order['status'] != 'مرفوض'): ?>
                        <form method="post" class="d-flex gap-1 align-items-center">
                            <input type="hidden" name="rare_order_id" value="<?=$order['id']?>">
                            <select name="status" class="form-select form-select-sm" style="min-width:120px;">
                                <?php foreach(['جديد','تم الاستقبال','جاري التحضير','تم الإرسال','تم الوصول','مرفوض'] as $st): ?>
                                    <option value="<?=$st?>" <?=$st==$order['status']?'selected':''?>><?=$st?></option>
                                <?php endforeach; ?>
                            </select>
                            <button class="btn btn-sm btn-outline-success" title="تحديث الحالة"><i class="bi bi-arrow-repeat"></i></button>
                        </form>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; if(empty($orders)): ?>
                <tr><td colspan="11" class="text-muted">لا توجد طلبات حالياً.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php include '../includes/footer.php'; ?>