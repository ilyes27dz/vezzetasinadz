<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($notif_count)) {
    // جلب عدد الإشعارات غير المقروءة + آخر 7 إشعارات
    require_once '../db.php';
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id=? AND is_read=0");
    $stmt->execute([$_SESSION['user_id'] ?? 0]);
    $notif_count = $stmt->fetchColumn();
    $stmt = $pdo->prepare("SELECT * FROM notifications WHERE user_id=? ORDER BY created_at DESC LIMIT 7");
    $stmt->execute([$_SESSION['user_id'] ?? 0]);
    $notifs = $stmt->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <title>لوحة تحكم المسؤول</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
    <style>
      body { background: #f7f7f7; }
      .admin-navbar {
        position: fixed;
        top: 0; right: 0; left: 0;
        height: 62px;
        background: #fff;
        color: #1d6a56;
        box-shadow: 0 2px 16px #14e49c18;
        z-index: 1100;
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0 32px 0 20px;
        border-bottom: 1.5px solid #e6fff6;
      }
      .admin-navbar .navbar-brand {
        font-weight: 700;
        font-size: 1.14rem;
        display: flex; align-items: center; gap: 10px;
        color: #14e49c;
        text-decoration: none;
      }
      .admin-navbar .notif-btn {
        position: relative;
        background: none;
        border: none;
        color: #14e49c;
        font-size: 1.6rem;
        margin-left: 15px;
        margin-right: 10px;
        outline: none;
        cursor: pointer;
        transition: color .15s;
      }
      .admin-navbar .notif-btn .notif-badge {
        position: absolute;
        top: 2px; left: 2px;
        font-size: 0.7rem;
        color: #fff;
        background: #ff274b;
        padding: 2px 6px;
        border-radius: 11px;
        font-weight: bold;
        box-shadow: 0 2px 8px #ff274b44;
      }
      .admin-navbar .logout-btn {
        background: #ff4c6b;
        color: #fff;
        padding: 8px 19px;
        border: none;
        border-radius: 12px;
        font-weight: bold;
        font-size: 1.05rem;
        display: flex;
        align-items: center;
        gap: 7px;
        margin-right: 10px;
        transition: background .16s, box-shadow .18s;
        box-shadow: 0 2px 18px #ff4c6b33;
        text-decoration:none;
      }
      .admin-navbar .logout-btn:hover { background: #d9002c; color: #fff; }
      /* إشعارات منبثقة */
      .notif-popover {
        position: fixed;
        top: 60px;
        right: 45px;
        width: 320px;
        background: #fff;
        border: 1.5px solid #d7f8ec;
        border-radius: 17px;
        box-shadow: 0 6px 28px #13e5a044, 0 1.5px 7px #14e49c29;
        z-index: 2002;
        display: block;
        animation: notifIn .19s;
      }
      @keyframes notifIn { from{opacity:0; transform:translateY(-18px);} to{ opacity:1; transform:none; } }
      .notif-popover-header {
        font-weight: bold;
        font-size: 1.09rem;
        padding: 11px 17px 9px 7px;
        border-bottom: 1px solid #e6fff6;
        display: flex; align-items: center; justify-content: space-between;
      }
      .notif-popover-body {
        max-height: 310px;
        overflow-y: auto;
        padding: 12px 15px 10px 10px;
      }
      .notif-item {
        background: #f9fcff;
        border-radius: 10px;
        margin-bottom: 8px;
        padding: 6px 8px 7px 4px;
        font-size: .97rem;
        border: 1px solid #f1fff9;
        box-shadow: 0 1.5px 5px #13e5a013;
      }
      .notif-item.new { background: #e8fff7; font-weight: bold;}
      .notif-title { margin-bottom: 2px; }
      .notif-date { font-size: .83rem; color: #888; }
      .notif-empty { color: #999; text-align: center; padding: 23px 0 21px 0; }
      .close-popover {
        border: none; background: transparent; font-size: 1.5rem; color: #999; cursor: pointer; margin-right: 6px;
      }
      @media (max-width: 600px) {
        .notif-popover { right: 5vw; width: 95vw; min-width:160px; }
      }
    </style>
</head>
<body class="admin-panel">
<!-- شريط علوي -->
<nav class="admin-navbar">
  <a class="navbar-brand" href="dashboard.php">
    <img src="https://cdn-icons-png.flaticon.com/512/3135/3135715.png" width="40" height="40" style="margin-left:8px;">
    لوحة تحكم المسؤول
  </a>
  <div class="d-flex align-items-center">
    <!-- زر الإشعارات -->
    <button class="notif-btn" id="notifBtn" type="button" title="الإشعارات">
      <i class="bi bi-bell"></i>
      <?php if(!empty($notif_count)): ?>
        <span class="notif-badge"><?=$notif_count?></span>
      <?php endif; ?>
    </button>
    <!-- قائمة الإشعارات المنبثقة -->
    <div class="notif-popover" id="notifPopover" style="display:none;">
      <div class="notif-popover-header">
        <span>الإشعارات</span>
        <button type="button" class="close-popover" id="closeNotifPopover">&times;</button>
      </div>
      <div class="notif-popover-body">
        <?php if(!empty($notifs)): foreach($notifs as $row): ?>
          <div class="notif-item<?=($row['is_read']==0?' new':'')?>">
            <div class="notif-title"><?=htmlspecialchars($row['title']??'إشعار')?></div>
            <div class="notif-date"><?=date('Y-m-d H:i',strtotime($row['created_at']))?></div>
          </div>
        <?php endforeach; else: ?>
          <div class="notif-empty">لا توجد إشعارات جديدة</div>
        <?php endif; ?>
      </div>
    </div>
    <a href="../auth/logout.php" class="logout-btn ms-2"><i class="bi bi-box-arrow-right"></i> خروج</a>
  </div>
</nav>
<script>
document.addEventListener('DOMContentLoaded', function() {
  var notifBtn = document.getElementById('notifBtn');
  var notifPopover = document.getElementById('notifPopover');
  var closeNotifPopover = document.getElementById('closeNotifPopover');
  notifBtn && notifBtn.addEventListener('click', function(e){
    notifPopover.style.display = notifPopover.style.display === 'block' ? 'none' : 'block';
    e.stopPropagation();
  });
  closeNotifPopover && closeNotifPopover.addEventListener('click', function(e){
    notifPopover.style.display = 'none';
    e.stopPropagation();
  });
  document.addEventListener('click', function(e){
    if(notifPopover && notifPopover.style.display === 'block'
      && !notifPopover.contains(e.target) && !notifBtn.contains(e.target)){
      notifPopover.style.display = 'none';
    }
  });
});
</script>