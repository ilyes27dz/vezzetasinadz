<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php"); exit;
}
require_once '../db.php';

if (isset($_GET['approve'])) {
    $id = intval($_GET['approve']);
    $pdo->prepare("UPDATE testimonials SET approved=1 WHERE id=?")->execute([$id]);
    header("Location: testimonials.php");
    exit;
}
if (isset($_GET['reject'])) {
    $id = intval($_GET['reject']);
    $pdo->prepare("DELETE FROM testimonials WHERE id=?")->execute([$id]);
    header("Location: testimonials.php");
    exit;
}
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $pdo->prepare("DELETE FROM testimonials WHERE id=?")->execute([$id]);
    header("Location: testimonials.php");
    exit;
}
$pending = $pdo->query("SELECT t.*, u.name FROM testimonials t JOIN users u ON t.user_id=u.id WHERE approved=0 ORDER BY created_at DESC")->fetchAll();
$approved = $pdo->query("SELECT t.*, u.name FROM testimonials t JOIN users u ON t.user_id=u.id WHERE approved=1 ORDER BY created_at DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8">
  <title>إدارة شهادات المرضى</title>
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
  <h2 class="mb-4 text-success"><i class="bi bi-chat-dots"></i> إدارة شهادات المرضى</h2>
  <h4 class="text-warning mb-3">قيد المراجعة</h4>
  <div class="row">
    <?php foreach($pending as $t): ?>
    <div class="col-md-6">
        <div class="card mb-3 shadow">
            <div class="card-body">
                <div class="mb-2"><i class="bi bi-person-circle"></i> <?=htmlspecialchars($t['name'])?> <?= $t['city'] ? '، '.htmlspecialchars($t['city']) : ''?></div>
                <div><?=nl2br(htmlspecialchars($t['content']))?></div>
                <div class="mt-3">
                    <a href="?approve=<?=$t['id']?>" class="btn btn-success btn-sm">قبول</a>
                    <a href="?reject=<?=$t['id']?>" onclick="return confirm('تأكيد حذف الشهادة؟')" class="btn btn-danger btn-sm">رفض</a>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
    <?php if(empty($pending)): ?>
    <div class="col-12"><div class="alert alert-info">لا توجد شهادات قيد المراجعة.</div></div>
    <?php endif; ?>
  </div>
  <h4 class="text-success mt-5 mb-3">الشهادات المقبولة</h4>
  <div class="row">
    <?php foreach($approved as $t): ?>
    <div class="col-md-6">
        <div class="card mb-3 shadow">
            <div class="card-body">
                <div class="mb-2"><i class="bi bi-person-circle"></i> <?=htmlspecialchars($t['name'])?> <?= $t['city'] ? '، '.htmlspecialchars($t['city']) : ''?></div>
                <div><?=nl2br(htmlspecialchars($t['content']))?></div>
                <div class="mt-2 text-muted small"><?=htmlspecialchars($t['created_at'])?></div>
                <div class="mt-2 text-end">
                    <a href="?delete=<?=$t['id']?>" onclick="return confirm('تأكيد حذف الشهادة؟')" class="btn btn-danger btn-sm"><i class="bi bi-trash"></i> حذف</a>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
    <?php if(empty($approved)): ?>
    <div class="col-12"><div class="alert alert-info">لا توجد شهادات مقبولة.</div></div>
    <?php endif; ?>
  </div>
</div>
</body>
</html>