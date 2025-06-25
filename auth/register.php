<?php
session_start();
require_once '../db.php';
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // معالجة رفع الصورة
    $photo = '';
    if (!empty($_FILES['photo']['name'])) {
        $ext = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
        $photo = time().rand(1000,9999).".".$ext;
        move_uploaded_file($_FILES['photo']['tmp_name'], "../uploads/".$photo);
    }

    // تحقق من البريد
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        $error = "البريد الإلكتروني مستخدم بالفعل!";
    } else {
        $stmt = $pdo->prepare("INSERT INTO users (name,email,password,phone,photo,role) VALUES (?,?,?,?,?,?)");
        $stmt->execute([$name, $email, $password, $phone, $photo, 'patient']);
        $_SESSION['user_id'] = $pdo->lastInsertId();
        $_SESSION['role'] = 'patient';
        header("Location: ../patient/index.php"); exit;
    }
}
?>
<?php include '../includes/header.php'; ?>
<div class="container py-5">
  <div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">
      <div class="card p-4 shadow">
        <h3 class="text-success text-center mb-3">تسجيل مريض جديد</h3>
        <?php if($error): ?><div class="alert alert-danger"><?=$error?></div><?php endif; ?>
        <form method="post" enctype="multipart/form-data">
          <div class="mb-3 text-center">
            <img src="../assets/img/profile.png" id="imgPreview" style="width:90px;height:90px;object-fit:cover;border-radius:30%;border:2px solidrgb(0, 0, 0);">
            <input type="file" name="photo" class="form-control mt-2" accept="image/*" onchange="document.getElementById('imgPreview').src=window.URL.createObjectURL(this.files[0])">
            <div class="form-text">اختياري: يمكنك رفع صورة شخصية</div>
          </div>
          <div class="mb-3">
            <label class="form-label">الاسم الكامل</label>
            <input type="text" class="form-control" name="name" required>
          </div>
          <div class="mb-3">
            <label class="form-label">البريد الإلكتروني</label>
            <input type="email" class="form-control" name="email" required>
          </div>
          <div class="mb-3">
            <label class="form-label">رقم الهاتف</label>
            <input type="text" class="form-control" name="phone">
          </div>
          <div class="mb-3">
            <label class="form-label">كلمة المرور</label>
            <input type="password" class="form-control" name="password" required>
          </div>
          <button class="btn btn-success w-100" type="submit">تسجيل</button>
          <div class="mt-3 text-center small">
            لديك حساب؟ <a href="login.php">دخول</a>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
<?php include '../includes/footer.php'; ?>