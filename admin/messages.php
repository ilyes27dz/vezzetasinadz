<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php"); exit;
}
require_once '../db.php';
if (isset($_GET['del'])) {
    $id = intval($_GET['del']);
    $pdo->prepare("DELETE FROM contact_messages WHERE id=?")->execute([$id]);
    header("Location: messages.php"); exit;
}
$rows = $pdo->query("SELECT * FROM contact_messages ORDER BY id DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8">
  <title>مراجعة رسائل التواصل</title>
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
  <h3 class="mb-4 text-success"><i class="bi bi-envelope"></i> مراجعة رسائل التواصل</h3>
  <div class="table-responsive">
    <table class="table table-bordered align-middle text-center bg-white shadow-sm">
      <thead class="table-info"><tr>
        <th>الاسم</th>
        <th>البريد</th>
        <th>الموضوع</th>
        <th>الرسالة</th>
        <th>تاريخ الإرسال</th>
        <th>حذف</th>
      </tr></thead>
      <tbody>
        <?php foreach($rows as $r): ?>
          <tr>
            <td><?=htmlspecialchars($r['name'])?></td>
            <td><?=htmlspecialchars($r['email'])?></td>
            <td><?=htmlspecialchars($r['subject'])?></td>
            <td><?=nl2br(htmlspecialchars($r['message']))?></td>
            <td><?=htmlspecialchars($r['sent_at'])?></td>
            <td>
              <a href="?del=<?=$r['id']?>" onclick="return confirm('تأكيد حذف الرسالة؟')" class="btn btn-danger btn-sm"><i class="bi bi-trash"></i> حذف</a>
            </td>
          </tr>
        <?php endforeach;?>
        <?php if(empty($rows)): ?>
          <tr><td colspan="6" class="text-muted">لا توجد رسائل.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
</body>
</html>