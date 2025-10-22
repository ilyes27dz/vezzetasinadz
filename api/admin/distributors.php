<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php"); exit;
}
require_once '../db.php';

$error = '';
$success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['addDistributor'])) {
    $name = trim($_POST['name']);
    $phone = trim($_POST['phone']);
    $city = trim($_POST['city']);
    if (!$name || !$phone || !$city) {
        $error = "يرجى ملء جميع الحقول!";
    } else {
        $stmt = $pdo->prepare("INSERT INTO distributors (name, phone, city) VALUES (?, ?, ?)");
        $stmt->execute([$name, $phone, $city]);
        $success = "تمت إضافة الموزع بنجاح!";
    }
}
if (isset($_GET['del'])) {
    $id = intval($_GET['del']);
    $pdo->prepare("DELETE FROM distributors WHERE id=?")->execute([$id]);
    header("Location: distributors.php?deleted=1"); exit;
}
if (isset($_GET['deleted'])) $success = "تم حذف الموزع بنجاح!";
$distributors = $pdo->query("SELECT * FROM distributors ORDER BY city, name")->fetchAll();
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8">
  <title>إدارة الموزعين</title>
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
    <div class="bg-white rounded-4 p-4 shadow-sm border mb-4">
        <h3 class="mb-3 text-success"><i class="bi bi-truck"></i> إدارة موزعي الأدوية</h3>
        <?php if($error): ?><div class="alert alert-danger"><?=$error?></div><?php endif; ?>
        <?php if($success): ?><div class="alert alert-success"><?=$success?></div><?php endif; ?>
        <form method="post" class="row g-3 mb-4">
            <input type="hidden" name="addDistributor" value="1">
            <div class="col-md-4">
                <input type="text" name="name" class="form-control" placeholder="اسم الموزع" required>
            </div>
            <div class="col-md-4">
                <input type="text" name="phone" class="form-control" placeholder="رقم الهاتف" required>
            </div>
            <div class="col-md-4">
                <input type="text" name="city" class="form-control" placeholder="المدينة" required>
            </div>
            <div class="col-12 text-end">
                <button class="btn btn-success px-4" type="submit"><i class="bi bi-plus"></i> إضافة موزع</button>
            </div>
        </form>
        <div class="table-responsive">
            <table class="table table-bordered align-middle text-center">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>الاسم</th>
                        <th>الهاتف</th>
                        <th>المدينة</th>
                        <th>إجراءات</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach($distributors as $d): ?>
                    <tr>
                        <td><?=$d['id']?></td>
                        <td><?=htmlspecialchars($d['name'])?></td>
                        <td><?=htmlspecialchars($d['phone'])?></td>
                        <td><?=htmlspecialchars($d['city'])?></td>
                        <td>
                            <a href="?del=<?=$d['id']?>" class="btn btn-sm btn-danger" onclick="return confirm('تأكيد حذف الموزع؟')">
                                <i class="bi bi-trash"></i> حذف
                            </a>
                        </td>
                    </tr>
                <?php endforeach;?>
                <?php if(empty($distributors)): ?>
                    <tr><td colspan="5" class="text-muted">لا يوجد موزعين بعد.</td></tr>
                <?php endif;?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</body>
</html>