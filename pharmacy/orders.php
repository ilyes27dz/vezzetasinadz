<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'pharmacy') {
    header("Location: ../auth/login.php"); exit;
}
$pharmacy_id = $_SESSION['pharmacy_id'];
require_once '../db.php';

// جلب الموزعين من قاعدة البيانات
$distributors = $pdo->query("SELECT * FROM distributors ORDER BY city, name")->fetchAll(PDO::FETCH_ASSOC);

// تغيير حالة الطلب
if (isset($_POST['order_id'], $_POST['status'])) {
    $order_id = intval($_POST['order_id']);
    $status = $_POST['status'];
    $allowed = ['جديد','تم الاستقبال','جاري التحضير','تم الإرسال'];
    if (in_array($status, $allowed)) {
        $stmt = $pdo->prepare("UPDATE drug_orders SET status=? WHERE id=? AND pharmacy_id=?");
        $stmt->execute([$status, $order_id, $pharmacy_id]);
    }
}

// تعيين موزع للطلب
if (isset($_POST['assign_distributor'], $_POST['order_id'], $_POST['distributor_id'])) {
    $order_id = intval($_POST['order_id']);
    $distributor_id = intval($_POST['distributor_id']);
    $stmt = $pdo->prepare("UPDATE drug_orders SET distributor_id=? WHERE id=? AND pharmacy_id=?");
    $stmt->execute([$distributor_id, $order_id, $pharmacy_id]);
}

// جلب الطلبيات مع بيانات الموزع المرتبط (إن وجد)
$orders = $pdo->prepare("
    SELECT o.*, u.email AS patient_email, u.phone AS patient_phone,
           d.name AS distributor_name, d.phone AS distributor_phone, d.city AS distributor_city
    FROM drug_orders o
    LEFT JOIN users u ON o.patient_id=u.id
    LEFT JOIN distributors d ON o.distributor_id=d.id
    WHERE o.pharmacy_id=?
    ORDER BY o.created_at DESC
");
$orders->execute([$pharmacy_id]);
$orders = $orders->fetchAll();

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
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="text-success mb-0"><i class="bi bi-truck"></i> تتبع الطلبيات</h3>
        <a href="dashboard.php" class="btn btn-outline-secondary"><i class="bi bi-arrow-right"></i> رجوع للواجهة</a>
    </div>
    <div class="card shadow p-3">
        <table class="table align-middle table-bordered table-hover text-center">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>الدواء</th>
                    <th>المريض/الزائر</th>
                    <th>الهاتف</th>
                    <th>الوصفة الطبية</th>
                    <th>السعر</th>
                    <th>طريقة الاستلام</th>
                    <th>ملاحظات</th>
                    <th>الموزع</th>
                    <th>الحالة</th>
                    <th>تغيير الحالة</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach($orders as $order): 
                $style = $statusStyles[$order['status']];
                $show_email = $order['patient_email'] ? $order['patient_email'] : $order['email'];
                $show_phone = $order['patient_phone'] ? $order['patient_phone'] : $order['phone'];
            ?>
                <tr>
                    <td><?=$order['id']?></td>
                    <td><?=htmlspecialchars($order['drug_name'])?></td>
                    <td><?=htmlspecialchars($show_email)?></td>
                    <td><?=htmlspecialchars($show_phone)?></td>
                    <td>
                        <?php if($order['prescription_file']): ?>
                            <a href="../prescriptions/<?=htmlspecialchars($order['prescription_file'])?>" class="btn btn-sm btn-info" target="_blank">
                                عرض الوصفة
                            </a>
                        <?php else: ?>
                            <span class="text-muted">لا توجد</span>
                        <?php endif; ?>
                    </td>
                    <td><?=$order['price']?> دج</td>
                    <td><?=$order['delivery']=='delivery'?'توصيل':'استلام'?></td>
                    <td><?=htmlspecialchars($order['notes'])?></td>
                    <td>
                        <?php if(!$order['distributor_id']): ?>
                            <form method="post" class="mb-0" style="min-width:160px">
                                <input type="hidden" name="order_id" value="<?=$order['id']?>">
                                <select name="distributor_id" class="form-select form-select-sm" required>
                                    <option value="">اختر موزع...</option>
                                    <?php foreach($distributors as $d): ?>
                                        <option value="<?=$d['id']?>"><?=$d['name']?> - <?=$d['city']?> (<?=$d['phone']?>)</option>
                                    <?php endforeach; ?>
                                </select>
                                <button name="assign_distributor" class="btn btn-sm btn-success mt-1">تعيين</button>
                            </form>
                        <?php else: ?>
                            <b><?=$order['distributor_name']?></b>
                            <div class="small"><?=htmlspecialchars($order['distributor_city'])?></div>
                            <a href="tel:<?=$order['distributor_phone']?>" class="small"><?=$order['distributor_phone']?></a>
                        <?php endif; ?>
                    </td>
                    <td>
                        <span class="badge bg-<?=$style['class']?> px-3 py-2">
                            <i class="bi <?=$style['icon']?>"></i>
                            <?=$order['status']?>
                        </span>
                    </td>
                    <td>
                        <?php if($order['status'] != 'تم الإرسال' && $order['status'] != 'تم الوصول'): ?>
                        <form method="post" class="d-flex gap-1 align-items-center">
                            <input type="hidden" name="order_id" value="<?=$order['id']?>">
                            <select name="status" class="form-select form-select-sm" style="min-width:120px;">
                                <?php foreach(['جديد','تم الاستقبال','جاري التحضير','تم الإرسال'] as $st): ?>
                                    <option value="<?=$st?>" <?=$st==$order['status']?'selected':''?>><?=$st?></option>
                                <?php endforeach; ?>
                            </select>
                            <button class="btn btn-sm btn-outline-success" title="تحديث الحالة"><i class="bi bi-arrow-repeat"></i></button>
                        </form>
                        <?php elseif($order['status'] == 'تم الإرسال'): ?>
                            <span class="text-primary">بانتظار تأكيد المريض</span>
                        <?php elseif($order['status'] == 'تم الوصول'): ?>
                            <span class="text-success fw-bold">تم الوصول للمريض</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if(empty($orders)): ?>
                <tr><td colspan="11" class="text-muted">لا توجد طلبيات حالياً.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php include '../includes/footer.php'; ?>