<?php
require_once __DIR__ . '/../../db.php';
if (session_status() === PHP_SESSION_NONE) session_start();
// جلب إشعارات المسؤول
$notif_count = 0;
$notif_list = [];
if (isset($_SESSION['user_id'])) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id=? AND is_read=0");
    $stmt->execute([$_SESSION['user_id']]);
    $notif_count = $stmt->fetchColumn();

    $stmt = $pdo->prepare("SELECT * FROM notifications WHERE user_id=? ORDER BY created_at DESC LIMIT 6");
    $stmt->execute([$_SESSION['user_id']]);
    $notif_list = $stmt->fetchAll();
}

// دالة تحدد الرابط المناسب حسب نوع الإشعار
function get_notification_link($notif) {
    // لو عندك حقول إضافية للمعرف يمكن تمريرها في الرابط (مثل id أو رقم الطلب) أضفها هنا
    switch ($notif['type']) {
        case 'appointment_request':
        case 'appointment_status':
        case 'appointment_confirmed':
            return '/ayloul_project/admin/appointments.php'; // صفحة إدارة المواعيد للأدمن
        case 'blood_request':
            return '/ayloul_project/admin/blood_requests.php'; // صفحة طلبات التبرع بالدم
        case 'message':
            return '/ayloul_project/admin/messages.php';
        case 'order_drug':
            return '/ayloul_project/admin/orders.php';
        case 'testimonials':
            return '/ayloul_project/admin/testimonials.php';
        case 'subscription':
            return '/ayloul_project/admin/subscriptions.php';
        case 'ad':
            return '/ayloul_project/admin/ads.php';
        // ... أضف المزيد حسب أنواع الإشعارات في نظامك
        default:
            return '/ayloul_project/admin/notifications.php'; // صفحة كل الإشعارات كخيار افتراضي
    }
}
?>
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
<style>
.top-navbar-admin {
  position: fixed; top: 0; right: 0; left: 0; z-index:1020;
  background: #fff; box-shadow: 0 2px 16px #14e49c22;
  min-height: 60px; display: flex; align-items: center; justify-content: space-between;
  padding: 0 28px 0 8px;
}
.top-navbar-admin .navbar-title {
  font-size: 1.13rem; font-weight: bold; color: #08956e;
  letter-spacing: .01em; display:flex;align-items:center;gap:8px;
}
.top-navbar-admin .admin-logo {
  width:38px;height:38px;border-radius:50%;background:#e4fff7;object-fit:cover;margin-left:10px;border:2px solid #14e49c;
}
.notif-bell-btn {
  position: relative;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  background: #fff;
  border: 2px solid #14e49c;
  color: #14e49c;
  border-radius: 50%;
  width: 44px;
  height: 44px;
  font-size: 1.43rem;
  margin: 0 10px 0 0;
  transition: background .14s, color .14s, box-shadow .17s;
  box-shadow: 0 2px 8px #14e49c22;
  cursor: pointer;
  outline: none;
  text-decoration: none;
}
.notif-bell-btn:hover, .notif-bell-btn:focus {
  background: #14e49c;
  color: #fff;
  box-shadow: 0 4px 18px #14e49c44;
  border-color: #14e49c;
}
.notif-badge {
  position: absolute;
  top: 3px;
  left: 7px;
  min-width: 20px;
  height: 20px;
  padding: 0 5px;
  background: #ff294e;
  color: #fff;
  font-size: .94rem;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: bold;
  border: 2px solid #fff;
  box-shadow: 0 2px 8px #ff294e50;
  z-index: 2;
}
.notif-dropdown-menu {
  max-height: 390px;
  overflow-y: auto;
  font-size: 1rem;
  min-width: 320px;
  max-width: 350px;
}
.top-navbar-admin .theme-btn {
  background: #e9f8f3; border:none; color: #14e49c; font-size: 1.5rem; border-radius: 50%;
  width: 40px; height: 40px; display: inline-flex; align-items: center; justify-content: center;
  margin-left: 8px;
}
.top-navbar-admin .logout-btn {
  background: #ff4c6b; color: #fff; border:none; border-radius: 18px; font-weight: bold; font-size: 1.08rem; padding: 7px 20px;
  box-shadow: 0 2px 12px #ff4c6b22; transition: background .14s; margin-right:8px;
}
.top-navbar-admin .logout-btn:hover { background: #d9002c; }
@media (max-width: 991px) {
  .top-navbar-admin { min-height: 54px; padding: 0 4px 0 2px;}
  .top-navbar-admin .admin-logo { width: 31px; height: 31px;}
  .top-navbar-admin .navbar-title {font-size:.97rem;}
  .notif-bell-btn { width:36px;height:36px;font-size:1.13rem;}
  .top-navbar-admin .theme-btn { width:32px; height:32px; font-size:1.15rem;}
  .top-navbar-admin .logout-btn { font-size: 0.97rem; padding: 6px 12px;}
  .notif-dropdown-menu { min-width: 95vw; }
}
</style>
<nav class="top-navbar-admin">
  <div class="d-flex align-items-center">
    <img src="https://cdn-icons-png.flaticon.com/512/3135/3135715.png" class="admin-logo" alt="admin">
    <span class="navbar-title"><i class="bi bi-grid-1x2"></i> لوحة تحكم المسؤول</span>
  </div>
  <div>
    <!-- زر الإشعارات المنسدل -->
    <div class="dropdown d-inline-block">
      <button class="notif-bell-btn" id="notifDropdownBtn" data-bs-toggle="dropdown" aria-expanded="false" title="الإشعارات">
        <i class="bi bi-bell"></i>
        <?php if($notif_count > 0): ?>
          <span class="notif-badge"><?= $notif_count ?></span>
        <?php endif; ?>
      </button>
      <ul class="dropdown-menu dropdown-menu-end notif-dropdown-menu shadow" aria-labelledby="notifDropdownBtn">
        <li class="dropdown-header fw-bold text-success"><i class="bi bi-bell-fill"></i> الإشعارات</li>
        <?php if (!$notif_list): ?>
          <li><div class="dropdown-item text-center text-muted small">لا توجد إشعارات جديدة</div></li>
        <?php else: foreach($notif_list as $n): ?>
          <li>
            <a class="dropdown-item d-flex align-items-start<?= $n['is_read'] ? '' : ' fw-bold text-dark' ?>"
               href="<?= htmlspecialchars(get_notification_link($n)) ?>">
              <i class="bi bi-dot <?= $n['is_read'] ? 'text-success' : 'text-danger' ?>" style="font-size:1.3em;margin-left:7px;"></i>
              <span style="line-height:1.4"><?= htmlspecialchars($n['message']) ?><br>
                <span class="small text-muted"><?= date('Y-m-d H:i', strtotime($n['created_at'])) ?></span>
              </span>
            </a>
          </li>
        <?php endforeach; ?>
          <li><hr class="dropdown-divider"></li>
          <li><a class="dropdown-item text-center text-primary" href="/ayloul_project/admin/notifications.php"><i class="bi bi-list"></i> عرض كل الإشعارات</a></li>
        <?php endif; ?>
      </ul>
    </div>
    <button id="toggleThemeBtn" class="theme-btn" title="الوضع الليلي/النهاري">
      <i class="bi bi-moon-stars" id="themeIcon"></i>
    </button>
    <a href="../auth/logout.php" class="logout-btn"><i class="bi bi-box-arrow-right"></i> خروج</a>
  </div>
</nav>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
// الوضع الليلي
const themeBtn = document.getElementById('toggleThemeBtn');
const themeIcon = document.getElementById('themeIcon');
function setTheme(night) {
  if(night){
    document.body.classList.add('dark-mode');
    themeIcon.classList.remove('bi-moon-stars');
    themeIcon.classList.add('bi-sun-fill');
    localStorage.setItem('theme','night');
  } else {
    document.body.classList.remove('dark-mode');
    themeIcon.classList.add('bi-moon-stars');
    themeIcon.classList.remove('bi-sun-fill');
    localStorage.setItem('theme','day');
  }
}
if(localStorage.getItem('theme')==='night'){ setTheme(true); }
themeBtn.onclick = function() {
  setTheme(!document.body.classList.contains('dark-mode'));
};
</script>