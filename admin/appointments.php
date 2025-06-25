<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php"); exit;
}
require_once '../db.php';
if (isset($_GET['del'])) {
    $id = intval($_GET['del']);
    $pdo->prepare("DELETE FROM appointments WHERE id=?")->execute([$id]);
    header("Location: appointments.php"); exit;
}
$rows = $pdo->query("SELECT a.*, u.name as patient FROM appointments a JOIN users u ON a.patient_id=u.id ORDER BY a.id DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8">
  <title>مراجعة المواعيد</title>
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
  <h3 class="mb-4 text-success"><i class="bi bi-calendar-check"></i> مراجعة المواعيد</h3>
  <div class="table-responsive">
    <table class="table table-bordered align-middle text-center bg-white shadow-sm">
      <thead class="table-success"><tr>
        <th>المريض</th>
        <th>الخدمة/الطبيب</th>
        <th>التاريخ</th>
        <th>التفاصيل</th>
        <th>الحالة</th>
        <th>حذف</th>
      </tr></thead>
      <tbody>
        <?php foreach($rows as $r): ?>
          <tr>
            <td><?=htmlspecialchars($r['patient'])?></td>
            <td><?=htmlspecialchars($r['service_type'])?> (<?=$r['service_id']?><?=$r['doctor_id'] ? '/'.$r['doctor_id'] : ''?>)</td>
            <td><?=htmlspecialchars($r['appointment_date'])?></td>
            <td><?=htmlspecialchars($r['details'])?></td>
            <td>
              <?php
              $status = strtolower($r['status']);
              if ($status == 'confirmed') {
                  echo '<span class="badge bg-success"><i class="bi bi-check-circle"></i> مؤكد</span>';
              } elseif ($status == 'pending') {
                  echo '<span class="badge bg-secondary"><i class="bi bi-clock-history"></i> قيد الدراسة</span>';
              } elseif ($status == 'cancelled' || $status == 'canceled') {
                  echo '<span class="badge bg-danger"><i class="bi bi-x-circle"></i> ملغى</span>';
              } elseif ($status == 'completed') {
                  echo '<span class="badge bg-info text-white"><i class="bi bi-check2-square"></i> مكتمل</span>';
              } else {
                  echo '<span class="badge bg-light text-dark">'.htmlspecialchars($r['status']).'</span>';
              }
              ?>
            </td>
            <td>
              <a href="?del=<?=$r['id']?>" onclick="return confirm('تأكيد حذف الموعد؟')" class="btn btn-danger btn-sm"><i class="bi bi-trash"></i> حذف</a>
            </td>
          </tr>
        <?php endforeach;?>
      </tbody>
    </table>
  </div>
</div>
</body>
</html>