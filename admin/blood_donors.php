<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php"); exit;
}
require_once '../db.php';
if(isset($_GET['accept'])){
    $pdo->prepare("UPDATE blood_donors SET is_active=1 WHERE id=?")->execute([$_GET['accept']]);
    header('Location: blood_donors.php?tab=pending'); exit;
}
if(isset($_GET['delete'])){
    $pdo->prepare("DELETE FROM blood_donors WHERE id=?")->execute([$_GET['delete']]);
    header('Location: blood_donors.php?tab=pending'); exit;
}
if(isset($_GET['delete_active'])){
    $pdo->prepare("DELETE FROM blood_donors WHERE id=?")->execute([$_GET['delete_active']]);
    header('Location: blood_donors.php?tab=active'); exit;
}
$tab = $_GET['tab'] ?? 'pending';
$pending = $pdo->query("SELECT * FROM blood_donors WHERE is_active=0 ORDER BY created_at DESC")->fetchAll();
$active = $pdo->query("SELECT * FROM blood_donors WHERE is_active=1 ORDER BY created_at DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8">
  <title>إدارة المتبرعين بالدم</title>
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
  <h2 class="mb-4 text-danger"><i class="bi bi-droplet-half"></i> إدارة المتبرعين بالدم</h2>
  <ul class="nav nav-tabs mb-4">
      <li class="nav-item">
          <a class="nav-link <?=(($tab=='pending')?'active':'')?>" href="?tab=pending">طلبات جديدة</a>
      </li>
      <li class="nav-item">
          <a class="nav-link <?=(($tab=='active')?'active':'')?>" href="?tab=active">المقبولون</a>
      </li>
  </ul>
  <?php if($tab=='pending'): ?>
      <?php if(!$pending): ?>
          <div class="alert alert-info">لا يوجد طلبات متبرعين جديدة حالياً.</div>
      <?php else: ?>
      <div class="table-responsive">
          <table class="table table-bordered align-middle">
              <thead>
                  <tr>
                      <th>الاسم</th>
                      <th>المدينة</th>
                      <th>زمرة الدم</th>
                      <th>رقم التواصل</th>
                      <th>تفاصيل</th>
                      <th>تاريخ الإضافة</th>
                      <th>إجراءات</th>
                  </tr>
              </thead>
              <tbody>
              <?php foreach($pending as $d): ?>
                  <tr>
                      <td><?=htmlspecialchars($d['donor_name'])?></td>
                      <td><?=htmlspecialchars($d['city'])?></td>
                      <td><?=htmlspecialchars($d['blood_type'])?></td>
                      <td><?=htmlspecialchars($d['contact'])?></td>
                      <td><?=htmlspecialchars($d['details'])?></td>
                      <td><?=htmlspecialchars($d['created_at'])?></td>
                      <td>
                          <a href="?accept=<?=$d['id']?>" class="btn btn-success btn-sm mb-1" onclick="return confirm('تأكيد قبول المتبرع؟')"><i class="bi bi-check-circle"></i> قبول</a>
                          <a href="?delete=<?=$d['id']?>" class="btn btn-danger btn-sm mb-1" onclick="return confirm('تأكيد حذف الطلب؟')"><i class="bi bi-trash"></i> حذف</a>
                      </td>
                  </tr>
              <?php endforeach;?>
              </tbody>
          </table>
      </div>
      <?php endif;?>
  <?php else: ?>
      <?php if(!$active): ?>
          <div class="alert alert-info">لا يوجد متبرعين مقبولين حالياً.</div>
      <?php else: ?>
      <div class="table-responsive">
          <table class="table table-bordered align-middle">
              <thead>
                  <tr>
                      <th>الاسم</th>
                      <th>المدينة</th>
                      <th>زمرة الدم</th>
                      <th>رقم التواصل</th>
                      <th>تفاصيل</th>
                      <th>تاريخ الإضافة</th>
                      <th>إجراءات</th>
                  </tr>
              </thead>
              <tbody>
              <?php foreach($active as $d): ?>
                  <tr>
                      <td><?=htmlspecialchars($d['donor_name'])?></td>
                      <td><?=htmlspecialchars($d['city'])?></td>
                      <td><?=htmlspecialchars($d['blood_type'])?></td>
                      <td><?=htmlspecialchars($d['contact'])?></td>
                      <td><?=htmlspecialchars($d['details'])?></td>
                      <td><?=htmlspecialchars($d['created_at'])?></td>
                      <td>
                          <a href="?delete_active=<?=$d['id']?>" class="btn btn-danger btn-sm mb-1" onclick="return confirm('تأكيد حذف المتبرع؟')"><i class="bi bi-trash"></i> حذف</a>
                      </td>
                  </tr>
              <?php endforeach;?>
              </tbody>
          </table>
      </div>
      <?php endif;?>
  <?php endif;?>
</div>
</body>
</html>