<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'patient') {
    header("Location: ../auth/login.php"); exit;
}
require_once '../db.php';
$error = '';
$success = '';
$stmt = $pdo->prepare("SELECT * FROM users WHERE id=?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $phone = trim($_POST['phone']);
    $photo = $user['photo'];
    if (!empty($_FILES['photo']['name'])) {
        if ($photo) @unlink("../uploads/".$photo);
        $ext = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
        $photo = time().rand(1000,9999).".".$ext;
        move_uploaded_file($_FILES['photo']['tmp_name'], "../uploads/".$photo);
    }
    $stmt = $pdo->prepare("UPDATE users SET name=?, phone=?, photo=? WHERE id=?");
    $stmt->execute([$name, $phone, $photo, $_SESSION['user_id']]);
    $success = "تم تحديث بياناتك بنجاح!";
    $user['name'] = $name;
    $user['phone'] = $phone;
    $user['photo'] = $photo;
}
?>
<?php include '../includes/header.php'; ?>
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-7">
            <div class="card p-4 shadow">
                <h3 class="text-success mb-4">تعديل الملف الشخصي</h3>
                <?php if($error): ?><div class="alert alert-danger"><?=$error?></div><?php endif; ?>
                <?php if($success): ?><div class="alert alert-success"><?=$success?></div><?php endif; ?>
                <form method="post" enctype="multipart/form-data">
...
<div class="mb-3 text-center">
    <img src="<?= (!empty($user['photo']) ? '../uploads/' . htmlspecialchars($user['photo']) : (file_exists('../assets/img/patient.png') ? '../assets/img/patient.png' : 'https://ui-avatars.com/api/?name=' . urlencode(isset($user['name']) ? $user['name'] : 'مريض') . '&background=36c66f&color=fff&rounded=true')) ?>"
         id="imgPreview" style="width:90px;height:90px;object-fit:cover;border-radius:50%;border:2px solid #36c66f;">
    <input type="file" name="photo" class="form-control mt-2" accept="image/*"
           onchange="document.getElementById('imgPreview').src=window.URL.createObjectURL(this.files[0])">
    <div class="form-text">يمكنك تغيير الصورة الشخصية</div>
</div>
...
                    <div class="mb-3">
                        <label class="form-label">الاسم الكامل</label>
                        <input type="text" class="form-control" name="name" value="<?=htmlspecialchars($user['name'])?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">رقم الهاتف</label>
                        <input type="text" class="form-control" name="phone" value="<?=htmlspecialchars($user['phone'])?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">البريد الإلكتروني</label>
                        <input type="email" class="form-control" value="<?=htmlspecialchars($user['email'])?>" disabled>
                    </div>
                    <button class="btn btn-success px-5" type="submit">حفظ التعديلات</button>
                    <a href="index.php" class="btn btn-secondary px-4 ms-2">رجوع</a>
                </form>
            </div>
        </div>
    </div>
</div>
<?php include '../includes/footer.php'; ?>