<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php"); exit;
}
require_once '../db.php';

// استقبال الفلتر
$role = isset($_GET['role']) && in_array($_GET['role'], ['patient','doctor']) ? $_GET['role'] : '';
$filter_param = $role ? "role=$role" : '';

// عمليات الاشتراك (تفعيل، رفض، إيقاف، تمديد، حذف)
if (isset($_GET['approve'])) {
    $id = intval($_GET['approve']);
    $pdo->prepare("UPDATE subscriptions SET status='active' WHERE id=?")->execute([$id]);
    $stmt = $pdo->prepare("SELECT user_id FROM subscriptions WHERE id=?"); $stmt->execute([$id]);
    $uid = $stmt->fetchColumn();
    if($uid) {
        require_once '../functions/notifications.php';
        add_notification($pdo, $uid, 'subscription_approved', $id, "تم تفعيل اشتراكك بنجاح!");
    }
    header("Location: subscriptions.php".($filter_param ? "?$filter_param" : "")); exit;
}
if (isset($_GET['reject'])) {
    $id = intval($_GET['reject']);
    $pdo->prepare("UPDATE subscriptions SET status='rejected' WHERE id=?")->execute([$id]);
    $stmt = $pdo->prepare("SELECT user_id FROM subscriptions WHERE id=?"); $stmt->execute([$id]);
    $uid = $stmt->fetchColumn();
    if($uid) {
        require_once '../functions/notifications.php';
        add_notification($pdo, $uid, 'subscription_rejected', $id, "تم رفض طلب اشتراكك.");
    }
    header("Location: subscriptions.php".($filter_param ? "?$filter_param" : "")); exit;
}
if(isset($_GET['pause'])) {
    $id = intval($_GET['pause']);
    $pdo->prepare("UPDATE subscriptions SET status='paused' WHERE id=?")->execute([$id]);
    header("Location: subscriptions.php".($filter_param ? "?$filter_param" : "")); exit;
}
if(isset($_GET['extend'])) {
    $id = intval($_GET['extend']);
    $pdo->prepare("UPDATE subscriptions SET end_date=DATE_ADD(end_date, INTERVAL 1 MONTH) WHERE id=?")->execute([$id]);
    header("Location: subscriptions.php".($filter_param ? "?$filter_param" : "")); exit;
}
if (isset($_GET['del'])) {
    $id = intval($_GET['del']);
    $pdo->prepare("DELETE FROM subscriptions WHERE id=?")->execute([$id]);
    header("Location: subscriptions.php".($filter_param ? "?$filter_param" : "")); exit;
}

// جلب الاشتراكات حسب الفلتر
$filter_sql = $role ? " WHERE u.role='$role' " : '';
$rows = $pdo->query("SELECT s.*, u.name as user, u.role FROM subscriptions s JOIN users u ON s.user_id=u.id $filter_sql ORDER BY s.id DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8">
  <title>مراجعة الاشتراكات</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@500;700&display=swap" rel="stylesheet">
  <style>
    body {background: #f7fcff;}
    .main-content-admin {margin-right: 240px; padding: 90px 36px 16px 16px; min-height: 100vh; font-family: 'Tajawal', 'Cairo', Tahoma, Arial, sans-serif; transition: background 0.2s;}
    @media (max-width:991px){.main-content-admin { margin-right: 0; padding: 70px 6px 10px 6px;}}
    .back-btn-top {margin-bottom: 18px; font-size: 1.07rem; font-weight: bold; border-radius: 12px; padding: 7px 20px; color: #0db98d; border: 2px solid #0db98d44; background: #e4fff7; transition: background .13s, color .13s; display: inline-flex; align-items: center; gap: 6px;}
    .back-btn-top:hover {background:#0db98d; color:#fff;}
    .btn.active {pointer-events: none;}
  </style>
</head>
<body>
<?php include 'includes/navbar.php'; ?>
<?php include 'includes/sidebar.php'; ?>
<div class="main-content-admin">
  <a href="dashboard.php" class="back-btn-top"><i class="bi bi-arrow-right"></i> رجوع للواجهة</a>
  <h3 class="mb-4 text-success fw-bold"><i class="bi bi-wallet2"></i> مراجعة الاشتراكات</h3>
  <div class="mb-2">
    <a href="subscriptions.php" class="btn btn-outline-secondary<?= !$role ? ' active' : '' ?>">الكل</a>
    <a href="subscriptions.php?role=patient" class="btn btn-outline-primary<?= $role=='patient' ? ' active' : '' ?>">اشتراكات المرضى</a>
    <a href="subscriptions.php?role=doctor" class="btn btn-outline-success<?= $role=='doctor' ? ' active' : '' ?>">اشتراكات الأطباء</a>
  </div>
  <div class="table-responsive">
    <table class="table table-bordered align-middle text-center bg-white shadow-sm" style="border-radius:16px;overflow:hidden;">
      <thead class="table-info"><tr>
        <th>الاسم</th>
        <th>الصفة</th>
        <th>تاريخ البداية</th>
        <th>تاريخ النهاية</th>
        <th>طريقة الدفع</th>
        <th>المرجع</th>
        <th>الحالة</th>
        <th>إجراءات</th>
      </tr></thead>
      <tbody>
        <?php foreach($rows as $r): ?>
          <tr>
            <td><?=htmlspecialchars($r['user'])?></td>
            <td>
              <?php if($r['role']=='doctor'): ?>
                <span class="badge bg-success">طبيب</span>
              <?php else: ?>
                <span class="badge bg-primary">مريض</span>
              <?php endif; ?>
            </td>
            <td><?=htmlspecialchars($r['start_date'])?></td>
            <td><?=htmlspecialchars($r['end_date'])?></td>
            <td><?=htmlspecialchars($r['payment_method'])?></td>
            <td><?=htmlspecialchars($r['payment_ref'])?></td>
            <td>
              <?php if($r['status'] == 'pending'): ?>
                <span class="badge bg-warning">بانتظار الموافقة</span>
              <?php elseif($r['status']=='active'): ?>
                <span class="badge bg-success">مفعّل</span>
              <?php elseif($r['status']=='rejected'): ?>
                <span class="badge bg-danger">مرفوض</span>
              <?php elseif($r['status']=='paused'): ?>
                <span class="badge bg-secondary">موقوف</span>
              <?php else: ?>
                <span class="badge bg-info"><?=$r['status']?></span>
              <?php endif; ?>
            </td>
            <td>
              <a href="subscription_invoice.php?id=<?=$r['id']?>" target="_blank" class="btn btn-info btn-sm"><i class="bi bi-printer"></i> فاتورة</a>
              <?php if($r['status']=='pending'): ?>
                <a href="subscriptions.php?approve=<?=$r['id']?><?=($role?"&role=$role":"")?>" class="btn btn-success btn-sm">تفعيل</a>
                <a href="subscriptions.php?reject=<?=$r['id']?><?=($role?"&role=$role":"")?>" class="btn btn-warning btn-sm">رفض</a>
              <?php elseif($r['status']=='active'): ?>
                <a href="subscriptions.php?pause=<?=$r['id']?><?=($role?"&role=$role":"")?>" class="btn btn-warning btn-sm">توقيف</a>
                <a href="subscriptions.php?extend=<?=$r['id']?><?=($role?"&role=$role":"")?>" class="btn btn-success btn-sm">تمديد شهر</a>
              <?php endif; ?>
              <a href="subscriptions.php?del=<?=$r['id']?><?=($role?"&role=$role":"")?>" onclick="return confirm('تأكيد حذف الاشتراك؟')" class="btn btn-danger btn-sm">حذف</a>
            </td>
          </tr>
        <?php endforeach;?>
        <?php if(empty($rows)): ?>
            <tr><td colspan="8" class="text-muted">لا توجد اشتراكات بعد.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
</body>
</html>