<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'pharmacy') { header("Location: ../auth/login.php"); exit; }
require_once '../db.php';

$pharmacy_id = $_SESSION['pharmacy_id'];

// إضافة دواء جديد
if(isset($_POST['add'])) {
    $name = trim($_POST['name']);
    $price = intval($_POST['price']);
    $is_available = isset($_POST['is_available']) ? 1 : 0;
    if($name && $price > 0) {
        $stmt = $pdo->prepare("INSERT INTO pharmacy_drugs (pharmacy_id, name, price, is_available) VALUES (?, ?, ?, ?)");
        $stmt->execute([$pharmacy_id, $name, $price, $is_available]);
        $msg = "تمت إضافة الدواء بنجاح";
    }
}

// حذف دواء
if(isset($_GET['del'])) {
    $id = intval($_GET['del']);
    $pdo->prepare("DELETE FROM pharmacy_drugs WHERE id=? AND pharmacy_id=?")->execute([$id, $pharmacy_id]);
    $msg = "تم حذف الدواء";
}

// تعديل دواء (مع معالجة حالة التوفر بشكل مضمون)
if(isset($_POST['edit'])) {
    $id = intval($_POST['id']);
    $name = trim($_POST['name']);
    $price = intval($_POST['price']);
    $is_available = isset($_POST['is_available']) ? 1 : 0;
    if($id && $name && $price > 0) {
        $pdo->prepare("UPDATE pharmacy_drugs SET name=?, price=?, is_available=? WHERE id=? AND pharmacy_id=?")
            ->execute([$name, $price, $is_available, $id, $pharmacy_id]);
        $msg = "تم تعديل الدواء";
    }
}

// جلب كل أدوية هذه الصيدلية
$stmt = $pdo->prepare("SELECT * FROM pharmacy_drugs WHERE pharmacy_id=? ORDER BY name");
$stmt->execute([$pharmacy_id]);
$drugs = $stmt->fetchAll();
?>
<?php include '../includes/header.php'; ?>
<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="mb-0 text-success"><i class="bi bi-capsule"></i> إدارة أدوية الصيدلية</h3>
        <a href="dashboard.php" class="btn btn-outline-secondary"><i class="bi bi-arrow-right"></i> رجوع </a>
    </div>

    <?php if(isset($msg)): ?><div class="alert alert-success"><?=$msg?></div><?php endif; ?>

    <div class="card p-3 mb-4">
        <form method="post" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label">اسم الدواء</label>
                <input type="text" name="name" class="form-control" required placeholder="اسم الدواء...">
            </div>
            <div class="col-md-3">
                <label class="form-label">السعر بالدج</label>
                <input type="number" name="price" class="form-control" min="1" required placeholder="مثال: 120">
            </div>
            <div class="col-md-3">
                <label class="form-label">متوفر حالياً؟</label><br>
                <div class="form-check form-switch d-inline-block">
                    <input class="form-check-input" type="checkbox" name="is_available" value="1" checked>
                    <label class="form-check-label">متوفر</label>
                </div>
            </div>
            <div class="col-md-2">
                <button class="btn btn-success w-100" name="add"><i class="bi bi-plus"></i> إضافة</button>
            </div>
        </form>
    </div>

    <div class="card p-3">
        <table class="table table-bordered table-striped text-center">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>اسم الدواء</th>
                    <th>السعر (دج)</th>
                    <th>متوفر</th>
                    <th>عمليات</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach($drugs as $d): ?>
                <tr>
                    <td><?=$d['id']?></td>
                    <td>
                        <input type="text" name="name" form="f<?=$d['id']?>" value="<?=htmlspecialchars($d['name'])?>" class="form-control form-control-sm" style="width:120px;">
                    </td>
                    <td>
                        <input type="number" name="price" form="f<?=$d['id']?>" value="<?=$d['price']?>" class="form-control form-control-sm" style="width:80px;">
                    </td>
                    <td>
                        <div class="form-check form-switch d-inline-block">
                            <input class="form-check-input" type="checkbox" name="is_available" form="f<?=$d['id']?>" value="1" <?=$d['is_available']?'checked':''?>>
                            <label class="form-check-label small"><?= $d['is_available'] ? 'متوفر' : 'غير متوفر' ?></label>
                        </div>
                    </td>
                    <td>
                        <form method="post" id="f<?=$d['id']?>" style="display:inline;">
                            <input type="hidden" name="id" value="<?=$d['id']?>">
                            <button class="btn btn-sm btn-primary" name="edit"><i class="bi bi-save"></i></button>
                        </form>
                        <a href="?del=<?=$d['id']?>" onclick="return confirm('تأكيد حذف الدواء؟')" class="btn btn-sm btn-danger"><i class="bi bi-trash"></i></a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php include '../includes/footer.php'; ?>