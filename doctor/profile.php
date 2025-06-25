<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'doctor') { header("Location: ../auth/login.php"); exit; }
require_once '../db.php';
$error = '';
$success = '';
$stmt = $pdo->prepare("SELECT u.*, d.* FROM users u JOIN doctors d ON u.id=d.user_id WHERE u.id=?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $phone = trim($_POST['phone']);
    $bio = trim($_POST['bio']);
    $address = trim($_POST['address']);
    $work_hours = trim($_POST['work_hours']);
    $photo = $user['photo'];
    if (!empty($_FILES['photo']['name'])) {
        if ($photo) @unlink("../uploads/".$photo);
        $ext = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
        $photo = time().rand(1000,9999).".".$ext;
        move_uploaded_file($_FILES['photo']['tmp_name'], "../uploads/".$photo);
    }
    $pdo->prepare("UPDATE users SET name=?, phone=? WHERE id=?")->execute([$name, $phone, $_SESSION['user_id']]);
    $pdo->prepare("UPDATE doctors SET bio=?, address=?, work_hours=?, photo=? WHERE user_id=?")
        ->execute([$bio, $address, $work_hours, $photo, $_SESSION['user_id']]);
    $success = "تم تحديث بياناتك بنجاح!";
    $user['name'] = $name;
    $user['phone'] = $phone;
    $user['bio'] = $bio;
    $user['address'] = $address;
    $user['work_hours'] = $work_hours;
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
                    <div class="mb-3 text-center">
                        <img src="<?=($user['photo']?'../uploads/'.htmlspecialchars($user['photo']):'../assets/img/doctor.png')?>" id="imgPreview" style="width:90px;height:90px;object-fit:cover;border-radius:50%;border:2px solid #36c66f;">
                        <input type="file" name="photo" class="form-control mt-2" accept="image/*"
                               onchange="document.getElementById('imgPreview').src=window.URL.createObjectURL(this.files[0])">
                        <div class="form-text">يمكنك تغيير الصورة الشخصية</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">الاسم الكامل</label>
                        <input type="text" class="form-control" name="name" value="<?=htmlspecialchars($user['name'])?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">رقم الهاتف</label>
                        <input type="text" class="form-control" name="phone" value="<?=htmlspecialchars($user['phone'])?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">العنوان التفصيلي</label>
                        <input type="text" class="form-control" name="address" value="<?=isset($user['address'])?htmlspecialchars($user['address']):''?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">أوقات العمل</label>
                        <input type="text" class="form-control" name="work_hours" value="<?=isset($user['work_hours'])?htmlspecialchars($user['work_hours']):''?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">نبذة عنك</label>
                        <textarea class="form-control" name="bio" rows="3"><?=htmlspecialchars($user['bio'])?></textarea>
                    </div>
                    <button class="btn btn-success px-5" type="submit">حفظ التعديلات</button>
                    <a href="index.php" class="btn btn-secondary px-4 ms-2">رجوع</a>
                </form>
            </div>
        </div>
    </div>
</div>
<?php include '../includes/footer.php'; ?>