<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php"); exit;
}
require_once '../db.php';
// عند الدخول: اجعل كل الإشعارات كمقروءة
$pdo->prepare("UPDATE notifications SET is_read=1 WHERE user_id=?")->execute([$_SESSION['user_id']]);
$notifs = $pdo->prepare("SELECT * FROM notifications WHERE user_id=? ORDER BY created_at DESC LIMIT 50");
$notifs->execute([$_SESSION['user_id']]);
$notifs = $notifs->fetchAll();
include 'includes/header.php';
?>
<div class="container-fluid">
  <div class="row">
    <div class="col-md-2 p-0">
      <?php include "includes/sidebar.php"; ?>
    </div>
    <main class="col-md-10 ms-auto p-4">
      <h3 class="mb-4"><i class="bi bi-bell"></i> الإشعارات</h3>
      <?php if(!$notifs): ?>
          <div class="alert alert-info">لا توجد إشعارات جديدة.</div>
      <?php else: ?>
          <ul class="list-group">
              <?php foreach($notifs as $n): ?>
                  <li class="list-group-item">
                      <span class="fw-bold"><?=htmlspecialchars($n['message'])?></span><br>
                      <span class="text-muted small"><?=htmlspecialchars($n['created_at'])?></span>
                  </li>
              <?php endforeach; ?>
          </ul>
      <?php endif; ?>
    </main>
  </div>
</div>
<?php include 'includes/footer.php'; ?>