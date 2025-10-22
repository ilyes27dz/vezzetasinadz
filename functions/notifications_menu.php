<?php
// تحميل db.php إذا لم يكن محمّلاً
if (!isset($pdo)) {
    require_once __DIR__ . '/../api/db.php';
}

// التأكد من وجود user_id
$user_id = $_SESSION['user_id'] ?? null;
$unread_count = 0;
$notifs = [];

if ($user_id) {
    // عدد الإشعارات غير المقروءة (PostgreSQL)
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id=? AND is_read=FALSE");
    $stmt->execute([$user_id]);
    $unread_count = $stmt->fetchColumn();

    // أحدث الإشعارات
    $stmt = $pdo->prepare("SELECT * FROM notifications WHERE user_id=? ORDER BY created_at DESC LIMIT 10");
    $stmt->execute([$user_id]);
    $notifs = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
<li class="nav-item dropdown">
    <a class="nav-link position-relative" href="#" id="notifDropdown" data-bs-toggle="dropdown" aria-expanded="false">
        <i class="bi bi-bell"></i>
        <?php if($unread_count > 0): ?>
            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                <?= $unread_count ?>
            </span>
        <?php endif; ?>
    </a>
    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="notifDropdown" style="min-width:290px;max-width:350px;">
        <li class="dropdown-header">الإشعارات</li>
        <?php if(empty($notifs)): ?>
            <li class="dropdown-item text-muted">لا توجد إشعارات</li>
        <?php else: ?>
            <?php foreach($notifs as $notif): ?>
                <li>
                    <a href="/notification_view.php?id=<?= $notif['id'] ?>" class="dropdown-item<?= !$notif['is_read'] ? ' fw-bold' : '' ?>">
                        <?= htmlspecialchars($notif['message']) ?>
                        <div class="small text-muted"><?= $notif['created_at'] ?></div>
                    </a>
                </li>
            <?php endforeach; ?>
        <?php endif; ?>
    </ul>
</li>
