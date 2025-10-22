<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'pharmacy') { header("Location: ../auth/login.php"); exit; }
require_once '../db.php';
$user_id = $_SESSION['user_id'];
$pharmacy_id = $_SESSION['pharmacy_id'];

$error = '';
$success = '';

// جلب معلومات المستخدم
$stmt = $pdo->prepare("SELECT u.email, u.fullname, u.phone, u.avatar, s.name as pharmacy_name FROM users u LEFT JOIN services s ON u.pharmacy_id=s.id WHERE u.id=?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullname = trim($_POST['fullname']);
    $phone = trim($_POST['phone']);
    $email = trim($_POST['email']);
    $pharmacy_name = trim($_POST['pharmacy_name']);
    $avatar = $user['avatar'];

    // معالجة رفع الصورة
    if(isset($_FILES['avatar']) && $_FILES['avatar']['error']==0){
        $ext = pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION);
        $newname = uniqid('pharm_').".".$ext;
        move_uploaded_file($_FILES['avatar']['tmp_name'], "../uploads/$newname");
        $avatar = $newname;
    }

    // تحديث users
    $stmt = $pdo->prepare("UPDATE users SET fullname=?, phone=?, email=?, avatar=? WHERE id=?");
    $stmt->execute([$fullname, $phone, $email, $avatar, $user_id]);
    // تحديث اسم الصيدلية
    $stmt2 = $pdo->prepare("UPDATE services SET name=? WHERE id=?");
    $stmt2->execute([$pharmacy_name, $pharmacy_id]);

    $success = "تم تحديث البيانات بنجاح!";
    // تحديث البيانات الحالية
    $user['fullname'] = $fullname;
    $user['phone'] = $phone;
    $user['email'] = $email;
    $user['avatar'] = $avatar;
    $user['pharmacy_name'] = $pharmacy_name;
}
?>
<?php include '../includes/header.php'; ?>
<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="mb-0 text-success"><i class="bi bi-person-circle"></i> تعديل المعلومات الشخصية</h3>
        <a href="dashboard.php" class="btn btn-outline-secondary"><i class="bi bi-arrow-right"></i> رجوع للواجهة</a>
    </div>
    <?php if($error): ?><div class="alert alert-danger"><?=$error?></div><?php endif; ?>
    <?php if($success): ?><div class="alert alert-success"><?=$success?></div><?php endif; ?>
    <div class="card p-4 mb-4">
        <form method="post" enctype="multipart/form-data" class="row g-3">
            <div class="col-md-3 text-center">
                <?php if($user['avatar']): ?>
                    <img src="../uploads/<?=$user['avatar']?>" class="rounded-circle mb-2" width="120" height="120" style="object-fit:cover">
                <?php else: ?>
                    <img src="../assets/pharmacy.png" class="rounded-circle mb-2" width="120" height="120" style="object-fit:cover">
                <?php endif; ?>
                <input type="file" name="avatar" accept="image/*" class="form-control mt-2">
            </div>
            <div class="col-md-9 row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">اسم المسؤول</label>
                    <input type="text" name="fullname" class="form-control" value="<?=htmlspecialchars($user['fullname'])?>">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">رقم الهاتف</label>
                    <input type="text" name="phone" class="form-control" value="<?=htmlspecialchars($user['phone'])?>">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">البريد الإلكتروني</label>
                    <input type="email" name="email" class="form-control" value="<?=htmlspecialchars($user['email'])?>">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">اسم الصيدلية</label>
                    <input type="text" name="pharmacy_name" class="form-control" value="<?=htmlspecialchars($user['pharmacy_name'])?>">
                </div>
            </div>
            <div class="col-12 text-end">
                <button class="btn btn-success"><i class="bi bi-save"></i> حفظ التعديلات</button>
            </div>
        </form>
    </div>
</div>
<?php include '../includes/footer.php'; ?>