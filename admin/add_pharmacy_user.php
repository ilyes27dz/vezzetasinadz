<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') { header("Location: ../auth/login.php"); exit; }
require_once '../db.php';

$success = '';
$error = '';
$pharmacies = $pdo->query("SELECT id, name FROM services WHERE type='pharmacy' ORDER BY name")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $pharmacy_id = intval($_POST['pharmacy_id']);

    $stmt = $pdo->prepare("SELECT id FROM users WHERE email=?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        $error = "هذا البريد الإلكتروني مستخدم بالفعل!";
    } else {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (email, password, role, pharmacy_id) VALUES (?, ?, 'pharmacy', ?)");
        $stmt->execute([$email, $hash, $pharmacy_id]);
        $success = "تم إنشاء حساب الصيدلية بنجاح!";
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8">
  <title>إضافة حساب صيدلية</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@500;700&display=swap" rel="stylesheet">
  <style>
    body {background: #f7fcff;}
    .main-content-admin {
      margin-right: 240px;
      padding: 90px 36px 16px 16px;
      min-height: 100vh;
      font-family: 'Tajawal', 'Cairo', Tahoma, Arial, sans-serif;
      transition: background 0.2s;
    }
    @media (max-width:991px){
      .main-content-admin { margin-right: 0; padding: 70px 6px 10px 6px;}
    }
    .back-btn-top {
      margin-bottom: 18px;
      font-size: 1.07rem;
      font-weight: bold;
      border-radius: 12px;
      padding: 7px 20px;
      color: #0db98d;
      border: 2px solid #0db98d44;
      background: #e4fff7;
      transition: background .13s, color .13s;
      display: inline-flex; align-items: center; gap: 6px;
    }
    .back-btn-top:hover {background:#0db98d; color:#fff;}
  </style>
</head>
<body>
<?php include 'includes/navbar.php'; ?>
<?php include 'includes/sidebar.php'; ?>
<div class="main-content-admin">
    <a href="dashboard.php" class="back-btn-top"><i class="bi bi-arrow-right"></i> رجوع للواجهة</a>
    <div class="card p-4 shadow">
        <h3 class="mb-4 text-success"><i class="bi bi-capsule-pill"></i> إضافة حساب صيدلية جديد</h3>
        <?php if($error): ?><div class="alert alert-danger"><?=$error?></div><?php endif; ?>
        <?php if($success): ?><div class="alert alert-success"><?=$success?></div><?php endif; ?>
        <form method="post" autocomplete="off">
            <div class="mb-3">
                <label class="form-label">البريد الإلكتروني</label>
                <input type="email" name="email" required class="form-control" placeholder="example@email.com">
            </div>
            <div class="mb-3">
                <label class="form-label">كلمة المرور</label>
                <input type="password" name="password" required class="form-control" placeholder="********">
            </div>
            <div class="mb-3">
                <label class="form-label">اختر الصيدلية</label>
                <select name="pharmacy_id" class="form-select" required>
                    <option value="">-- اختر صيدلية --</option>
                    <?php foreach($pharmacies as $ph): ?>
                        <option value="<?=$ph['id']?>"><?=$ph['name']?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button class="btn btn-success" type="submit"><i class="bi bi-plus"></i> إضافة الحساب</button>
        </form>
    </div>
</div>
</body>
</html>