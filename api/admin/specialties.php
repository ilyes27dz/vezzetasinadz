<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php"); exit;
}
require_once '../db.php';
// إضافة تخصص
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['name'])) {
    $stmt = $pdo->prepare("INSERT INTO specialties (name) VALUES (?)");
    $stmt->execute([trim($_POST['name'])]);
    header("Location: specialties.php"); exit;
}
// حذف تخصص
if (isset($_GET['del'])) {
    $pdo->prepare("DELETE FROM specialties WHERE id=?")->execute([intval($_GET['del'])]);
    header("Location: specialties.php"); exit;
}
$rows = $pdo->query("SELECT * FROM specialties ORDER BY name")->fetchAll();
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8">
  <title>إدارة التخصصات الطبية</title>
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
  <h3 class="mb-4 text-success"><i class="bi bi-list-ul"></i> إدارة التخصصات الطبية</h3>
  <form method="post" class="row g-2 align-items-end mb-4 bg-white p-3 rounded-3 shadow-sm">
    <div class="col-md-7"><input required type="text" name="name" class="form-control" placeholder="اسم التخصص الطبي"></div>
    <div class="col-md-3"><button type="submit" class="btn btn-success"><i class="bi bi-plus"></i> إضافة تخصص</button></div>
  </form>
  <div class="card p-3">
    <table class="table table-bordered bg-white shadow-sm">
      <thead class="table-primary"><tr><th>التخصص</th><th>حذف</th></tr></thead>
      <tbody>
      <?php foreach($rows as $r): ?>
        <tr>
          <td><?=htmlspecialchars($r['name'])?></td>
          <td><a href="?del=<?=$r['id']?>" onclick="return confirm('تأكيد حذف التخصص؟')" class="btn btn-danger btn-sm"><i class="bi bi-trash"></i> حذف</a></td>
        </tr>
      <?php endforeach;?>
      <?php if(empty($rows)): ?>
        <tr><td colspan="2" class="text-muted">لا توجد تخصصات بعد.</td></tr>
      <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
</body>
</html>