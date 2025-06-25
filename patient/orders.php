<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'patient') { header("Location: ../auth/login.php"); exit; }
require_once '../db.php';
$patient_id = $_SESSION['user_id'];

// تأكيد استلام الدواء
if(isset($_POST['confirm_received'], $_POST['order_id'])) {
    $order_id = intval($_POST['order_id']);
    // حتى لا يمكن التأكيد إلا إذا كانت الحالة "تم الإرسال"
    $stmt = $pdo->prepare("UPDATE drug_orders SET status='تم الوصول' WHERE id=? AND patient_id=? AND status='تم الإرسال'");
    $stmt->execute([$order_id, $patient_id]);
}

// جلب جميع الطلبات مع بيانات الموزع
$stmt = $pdo->prepare("SELECT o.*, s.name as pharmacy_name, d.name as distributor_name, d.phone as distributor_phone, d.city as distributor_city
    FROM drug_orders o 
    LEFT JOIN services s ON o.pharmacy_id=s.id
    LEFT JOIN distributors d ON o.distributor_id=d.id
    WHERE o.patient_id=? ORDER BY o.created_at DESC");
$stmt->execute([$patient_id]);
$orders = $stmt->fetchAll();

$statusStyles = [
    'جديد'         => ['class'=>'secondary','icon'=>'bi-clock'],
    'تم الاستقبال' => ['class'=>'info','icon'=>'bi-person-check'],
    'جاري التحضير' => ['class'=>'warning','icon'=>'bi-hourglass-split'],
    'تم الإرسال'   => ['class'=>'primary','icon'=>'bi-truck'],
    'تم الوصول'    => ['class'=>'success','icon'=>'bi-check-circle'],
    'ملغى'         => ['class'=>'danger','icon'=>'bi-x-circle'],
];
?>
<?php include '../includes/header.php'; ?>
<div class="container py-5">
    <a href="index.php" class="btn btn-outline-secondary mb-3"><i class="bi bi-arrow-right"></i> رجوع</a>
    <div class="card shadow p-3">
        <h3 class="mb-4 text-success"><i class="bi bi-truck"></i> طلبياتي</h3>
        <table class="table align-middle table-bordered table-hover text-center">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>الصيدلية</th>
                    <th>الدواء</th>
                    <th>السعر</th>
                    <th>طريقة الاستلام</th>
                    <th>الموزع</th>
                    <th>ملاحظات</th>
                    <th>تاريخ الطلب</th>
                    <th>الحالة</th>
                    <th>تأكيد الاستلام</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach($orders as $order): 
                $style = $statusStyles[$order['status']];
            ?>
                <tr>
                    <td><?=$order['id']?></td>
                    <td><?=htmlspecialchars($order['pharmacy_name'])?></td>
                    <td><?=$order['drug_name']?></td>
                    <td><?=$order['price']?> دج</td>
                    <td><?=$order['delivery']=='delivery'?'توصيل':'استلام'?></td>
                    <td>
                        <?php if($order['distributor_id']): ?>
                            <b><?=htmlspecialchars($order['distributor_name'])?></b><br>
                            <span class="small"><?=htmlspecialchars($order['distributor_city'])?></span><br>
                            <a href="tel:<?=htmlspecialchars($order['distributor_phone'])?>"><?=htmlspecialchars($order['distributor_phone'])?></a>
                        <?php else: ?>
                            <span class="text-muted">لم يتم التعيين بعد</span>
                        <?php endif; ?>
                    </td>
                    <td><?=htmlspecialchars($order['notes'])?></td>
                    <td><?=$order['created_at']?></td>
                    <td>
                        <span class="badge bg-<?=$style['class']?> px-3 py-2">
                            <i class="bi <?=$style['icon']?>"></i>
                            <?=$order['status']?>
                        </span>
                    </td>
                    <td>
                        <?php if($order['status'] == 'تم الإرسال'): ?>
                            <form method="post" class="d-inline">
                                <input type="hidden" name="order_id" value="<?=$order['id']?>">
                                <button name="confirm_received" class="btn btn-success btn-sm">تم الاستلام</button>
                            </form>
                        <?php elseif($order['status'] == 'تم الوصول'): ?>
                            <span class="text-success fw-bold">تم التأكيد</span>
                        <?php else: ?>
                            <span class="text-muted">---</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if(empty($orders)): ?>
                <tr><td colspan="10" class="text-muted">لا توجد طلبيات.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php include '../includes/footer.php'; ?>