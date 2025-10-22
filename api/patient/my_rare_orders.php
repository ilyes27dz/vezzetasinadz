<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'patient') exit;
require_once '../db.php';

$patient_id = $_SESSION['user_id'];

// تأكيد استلام الدواء النادر
if(isset($_POST['confirm_received'], $_POST['rare_order_id'])) {
    $rare_order_id = intval($_POST['rare_order_id']);
    // لا يمكن التأكيد إلا إذا كانت الحالة "تم الإرسال"
    $stmt = $pdo->prepare("UPDATE rare_drug_requests SET status='تم الوصول' WHERE id=? AND patient_id=? AND status='تم الإرسال'");
    $stmt->execute([$rare_order_id, $patient_id]);
}

// جلب جميع الطلبات للأدوية النادرة
$stmt = $pdo->prepare(
    "SELECT r.*, d.name AS drug_name, d.category, s.name AS pharmacy_name
     FROM rare_drug_requests r
     JOIN rare_drugs d ON r.drug_id = d.id
     JOIN services s ON r.pharmacy_id = s.id
     WHERE r.patient_id = ?
     ORDER BY r.requested_at DESC"
);
$stmt->execute([$patient_id]);
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
    <a href="rare_drugs.php" class="btn btn-outline-secondary mb-3"><i class="bi bi-arrow-right"></i> رجوع</a>
    <h3 class="mb-4 text-danger"><i class="bi bi-exclamation-circle"></i> تتبع طلباتي للأدوية النادرة</h3>
    <div class="card p-3">
        <table class="table align-middle table-bordered table-hover text-center">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>الصيدلية</th>
                    <th>اسم الدواء</th>
                    <th>الفئة</th>
                    <th>الوصفة الطبية</th>
                    <th>ملاحظات</th>
                    <th>تاريخ الطلب</th>
                    <th>الحالة</th>
                    <th>تأكيد الاستلام</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach($orders as $i => $order): 
                $status = $order['status'];
                $style = $statusStyles[$status] ?? ['class'=>'light','icon'=>'bi-question-circle'];
            ?>
                <tr class="<?= $status=='تم الاستقبال' ? 'table-info' : '' ?>">
                    <td><?= $i+1 ?></td>
                    <td><?= htmlspecialchars($order['pharmacy_name']) ?></td>
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
                            <?=$status?>
                            <?php if($status=='تم الاستقبال'): ?>
                                <div class="text-success fw-bold d-block">تم استقبال الطلب من الصيدلية</div>
                            <?php endif; ?>
                        </span>
                    </td>
                    <td>
                        <?php if($status == 'تم الإرسال'): ?>
                            <form method="post" class="d-inline">
                                <input type="hidden" name="rare_order_id" value="<?=$order['id']?>">
                                <button name="confirm_received" class="btn btn-success btn-sm">تم الاستلام</button>
                            </form>
                        <?php elseif($status == 'تم الوصول'): ?>
                            <span class="text-success fw-bold">تم التأكيد</span>
                        <?php else: ?>
                            <span class="text-muted">---</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; if(empty($orders)): ?>
                <tr><td colspan="9" class="text-muted">لا توجد طلبات للأدوية النادرة.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php include '../includes/footer.php'; ?>