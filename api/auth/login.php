<?php
session_start();
require_once '../db.php';
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email=?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $user['role'];

        // لو المستخدم صيدلية، خزّن pharmacy_id من جدول المستخدمين
        if ($user['role'] == 'pharmacy') {
            $_SESSION['pharmacy_id'] = $user['pharmacy_id']; // تأكد أن لديك هذا الحقل في جدول users
            header("Location: ../pharmacy/dashboard.php");
        }

        
        elseif ($user['role'] == 'admin') {
            header("Location: ../admin/dashboard.php");
        }
        elseif ($user['role'] == 'doctor') {
            header("Location: ../doctor/index.php");
        }
        elseif ($user['role'] == 'patient') {
            header("Location: ../patient/index.php");
        }
        else {
            header("Location: ../index.php");
        }
        exit;
    } else {
        $error = "بيانات الدخول غير صحيحة!";
    }
}
?>
<?php include '../includes/header.php'; ?>
<div class="container py-5">
  <div class="row justify-content-center">
    <div class="col-md-5">
      <div class="card p-4 shadow">
        <h3 class="text-success mb-3 text-center">تسجيل الدخول</h3>
        <?php if($error): ?><div class="alert alert-danger"><?=$error?></div><?php endif; ?>
        <form method="post">
          <div class="mb-3">
            <label class="form-label">البريد الإلكتروني</label>
            <input type="email" class="form-control" name="email" required autocomplete="username">
          </div>
          <div class="mb-3">
            <label class="form-label">كلمة المرور</label>
            <input type="password" class="form-control" name="password" required autocomplete="current-password">
          </div>
          <button class="btn btn-success w-100" type="submit">دخول</button>
          <div class="mt-3 text-center small">
            مريض جديد؟ <a href="register.php">سجّل مجاناً</a>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
<?php include '../includes/footer.php'; ?>