<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') { header("Location: ../auth/login.php"); exit; }
require_once '../db.php';

if(isset($_POST['add'])) {
    $name = trim($_POST['name']);
    $price = intval($_POST['price']);
    if($name && $price > 0) {
        $stmt = $pdo->prepare("INSERT INTO drugs (name, price) VALUES (?, ?)");
        $stmt->execute([$name, $price]);
        $msg = "تمت إضافة الدواء بنجاح";
    }
}
if(isset($_GET['del'])) {
    $id = intval($_GET['del']);
    $pdo->prepare("DELETE FROM drugs WHERE id=?")->execute([$id]);
    $msg = "تم حذف الدواء";
}
if(isset($_POST['edit'])) {
    $id = intval($_POST['id']);
    $name = trim($_POST['name']);
    $price = intval($_POST['price']);
    if($id && $name && $price > 0) {
        $pdo->prepare("UPDATE drugs SET name=?, price=? WHERE id=?")->execute([$name, $price, $id]);
        $msg = "تم تعديل الدواء";
    }
}
$drugs = $pdo->query("SELECT * FROM drugs ORDER BY name")->fetchAll();
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8">
  <title>إدارة الأدوية</title>
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
  <h2 class="mb-4 text-success"><i class="bi bi-capsule"></i> إدارة الأدوية</h2>
  <?php if(isset($msg)): ?><div class="alert alert-success"><?=$msg?></div><?php endif; ?>

  <div class="card p-3 mb-4">
      <form method="post" class="row g-3 align-items-end">
          <div class="col-md-5">
              <label class="form-label">اسم الدواء</label>
              <input type="text" name="name" class="form-control" required placeholder="اسم الدواء...">
          </div>
          <div class="col-md-4">
              <label class="form-label">السعر بالدج</label>
              <input type="number" name="price" class="form-control" min="1" required placeholder="مثال: 120">
          </div>
          <div class="col-md-3">
              <button class="btn btn-success" name="add"><i class="bi bi-plus"></i> إضافة</button>
          </div>
      </form>
  </div>

  <div class="card p-3">
      <table class="table table-bordered table-striped text-center bg-white shadow-sm">
          <thead class="table-light">
              <tr>
                  <th>#</th>
                  <th>اسم الدواء</th>
                  <th>السعر (دج)</th>
                  <th>عمليات</th>
              </tr>
          </thead>
          <tbody>
          <?php foreach($drugs as $d): ?>
              <tr>
                  <td><?=$d['id']?></td>
                  <td>
                      <form method="post" class="d-inline-flex align-items-center">
                          <input type="hidden" name="id" value="<?=$d['id']?>">
                          <input type="text" name="name" value="<?=htmlspecialchars($d['name'])?>" class="form-control form-control-sm" style="width:120px;">
                  </td>
                  <td>
                          <input type="number" name="price" value="<?=$d['price']?>" class="form-control form-control-sm" style="width:80px;">
                  </td>
                  <td>
                          <button class="btn btn-sm btn-primary" name="edit"><i class="bi bi-save"></i></button>
                      </form>
                      <a href="?del=<?=$d['id']?>" onclick="return confirm('تأكيد حذف الدواء؟')" class="btn btn-sm btn-danger"><i class="bi bi-trash"></i></a>
                  </td>
              </tr>
          <?php endforeach; ?>
          <?php if(empty($drugs)): ?>
              <tr><td colspan="4" class="text-muted">لا توجد أدوية بعد.</td></tr>
          <?php endif; ?>
          </tbody>
      </table>
  </div>
</div>
</body>
</html>