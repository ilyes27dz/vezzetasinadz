<link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@500;700&display=swap" rel="stylesheet">
<style>
:root {
  --sidebar-bg: #161e2e;
  --sidebar-accent: #14e49c;
  --sidebar-hover: #232b38;
  --sidebar-icon: #14e49c;
  --sidebar-shadow: 0 2px 24px #12e29d22;
  --sidebar-width: 245px;
  --sidebar-mobile-width: 82vw;
  --sidebar-text: #e6fff7;
  --logout-bg: linear-gradient(90deg, #ff4c6b 60%, #ff194a 100%);
  --logout-bg-hover: linear-gradient(90deg, #d9002c 60%, #ff194a 100%);
}
body, html { font-family: 'Tajawal', 'Cairo', Tahoma, Arial, sans-serif !important; }
.admin-sidebar {
  position: fixed;
  top: 0; right: 0; bottom: 0;
  width: var(--sidebar-width);
  background: var(--sidebar-bg);
  box-shadow: var(--sidebar-shadow);
  z-index: 1000;
  padding: 0;
  display: flex;
  flex-direction: column;
  align-items: stretch;
  transition: right 0.3s cubic-bezier(0.77,0,0.175,1);
  overflow-y: auto;
  max-height: 100vh;
  margin-top: 60px;
  border-left: 4px solid #14e49c33;
}
.admin-sidebar .sidebar-header {
  font-weight: 700;
  color: #fff;
  font-size: 1.22rem;
  letter-spacing: .04em;
  margin: 26px 0 15px 0;
  text-align: center;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 10px;
  user-select: none;
}
.admin-sidebar .sidebar-header i {
  color: var(--sidebar-accent);
  font-size: 1.5rem;
}
.admin-sidebar .nav {
  flex: 1 1 0;
  display: flex;
  flex-direction: column;
  gap: 2px;
}
.admin-sidebar .nav-link {
  color: var(--sidebar-text);
  font-weight: 500;
  border-radius: 40px 0 0 40px;
  margin: 0 12px 4px 0;
  padding: 13px 24px 13px 16px;
  font-size: 1.1rem;
  display: flex;
  align-items: center;
  gap: 13px;
  transition: background .15s,color .18s, box-shadow .2s;
  text-align: right;
  position: relative;
  background: none;
  border: none;
  outline: none;
  cursor: pointer;
  min-height: 44px;
  letter-spacing: .01em;
}
.admin-sidebar .nav-link.active, .admin-sidebar .nav-link:hover {
  background: var(--sidebar-hover);
  color: var(--sidebar-accent);
  font-weight: 700;
  box-shadow: 0 2px 18px #13e5a012;
  text-decoration: none;
}
.admin-sidebar .nav-link.active::before, .admin-sidebar .nav-link:hover::before {
  content: '';
  position: absolute;
  right: 0; top: 10%; bottom: 10%;
  width: 5px;
  border-radius: 4px 0 0 4px;
  background: var(--sidebar-accent);
}
.admin-sidebar .nav-link i {
  font-size: 1.38rem;
  color: var(--sidebar-icon);
  min-width: 27px;
  text-align: center;
  transition: color .15s;
}

.admin-sidebar .sidebar-footer {
  margin-top: auto;
  padding: 8px 0 18px 0;
  display: flex;
  flex-direction: column;
  align-items: stretch;
  gap: 0;
}
.admin-sidebar .sidebar-logout {
  border: none;
  background: var(--logout-bg);
  color: #fff;
  font-weight: 500;
  font-size: 1.07rem;
  border-radius: 40px 0 0 40px;
  padding: 11px 24px 11px 16px;
  margin: 0 12px 0 0;
  box-shadow: 0 2px 18px #ff4c6b22;
  display: flex;
  align-items: center;
  gap: 13px;
  cursor: pointer;
  transition: background .14s, box-shadow .13s, color .13s;
  min-height: 44px;
}
.admin-sidebar .sidebar-logout i { font-size: 1.23rem; }
.admin-sidebar .sidebar-logout:hover, .admin-sidebar .sidebar-logout:focus {
  background: var(--logout-bg-hover);
  color: #fff;
  box-shadow: 0 0 24px #ff4c6baa;
}
.sidebar-mobile-toggle {
  display: none;
}
@media (max-width: 991px) {
  .admin-sidebar {
    width: var(--sidebar-mobile-width);
    right: -105vw;
    box-shadow: none;
    border-radius: 0 0 0 22px;
    padding-bottom: 60px;
    max-height: 100vh;
    margin-top: 54px;
  }
  .admin-sidebar.show {
    right: 0;
    box-shadow: 0 0 40px #14e49c29;
  }
  .sidebar-mobile-toggle {
    display: block;
    position: fixed;
    top: 17px; right: 19px;
    z-index: 1200;
    background: var(--sidebar-accent);
    color: #fff;
    border-radius: 50%;
    border: none;
    width: 48px; height: 48px;
    font-size: 2.1rem;
    box-shadow: 0 2px 18px #13e5a022;
    cursor: pointer;
    outline: none;
  }
  body.sidebar-open {
    overflow: hidden;
  }
}
::-webkit-scrollbar {
  width: 7px;
  background: #111b24;
}
::-webkit-scrollbar-thumb {
  background: #14e49c66;
  border-radius: 7px;
}
</style>
<!-- Mobile Toggle Button -->
<button class="sidebar-mobile-toggle" id="sidebarToggleBtn" title="القائمة">
  <i class="bi bi-list"></i>
</button>
<nav class="admin-sidebar" id="adminSidebar">
  <div class="sidebar-header"><i class="bi bi-shield-check"></i> مسؤول المنصة</div>
  <div class="nav flex-column">
    <a class="nav-link <?=basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''?>" href="dashboard.php"><i class="bi bi-house"></i> الرئيسية</a>
    <a class="nav-link <?=basename($_SERVER['PHP_SELF']) == 'doctors.php' ? 'active' : ''?>" href="doctors.php"><i class="bi bi-person-badge"></i> الأطباء</a>
    <a class="nav-link <?=basename($_SERVER['PHP_SELF']) == 'appointments.php' ? 'active' : ''?>" href="appointments.php"><i class="bi bi-calendar-check"></i> المواعيد</a>
    <a class="nav-link <?=basename($_SERVER['PHP_SELF']) == 'testimonials.php' ? 'active' : ''?>" href="testimonials.php"><i class="bi bi-chat-dots"></i> التعليقات</a>
    <a class="nav-link <?=basename($_SERVER['PHP_SELF']) == 'ads.php' ? 'active' : ''?>" href="ads.php"><i class="bi bi-megaphone"></i> الإعلانات</a>
    <a class="nav-link <?=basename($_SERVER['PHP_SELF']) == 'blood_requests.php' ? 'active' : ''?>" href="blood_requests.php"><i class="bi bi-droplet-half"></i> طلبات التبرع بالدم</a>
    <a class="nav-link <?=basename($_SERVER['PHP_SELF']) == 'blood_donors.php' ? 'active' : ''?>" href="blood_donors.php"><i class="bi bi-droplet"></i> متبرعو الدم</a>
    <a class="nav-link <?=basename($_SERVER['PHP_SELF']) == 'services.php' ? 'active' : ''?>" href="services.php"><i class="bi bi-list-ul"></i> الخدمات</a>
    <a class="nav-link <?=basename($_SERVER['PHP_SELF']) == 'distributors.php' ? 'active' : ''?>" href="distributors.php"><i class="bi bi-truck"></i> الموزعون</a>
    <a class="nav-link <?=basename($_SERVER['PHP_SELF']) == 'add_pharmacy_user.php' ? 'active' : ''?>" href="add_pharmacy_user.php"><i class="bi bi-capsule-pill"></i> إضافة صيدلية</a>
    <a class="nav-link <?=basename($_SERVER['PHP_SELF']) == 'subscriptions.php' ? 'active' : ''?>" href="subscriptions.php"><i class="bi bi-wallet2"></i> الاشتراكات</a>
    <a class="nav-link <?=basename($_SERVER['PHP_SELF']) == 'messages.php' ? 'active' : ''?>" href="messages.php"><i class="bi bi-envelope"></i> الرسائل</a>
    <a class="nav-link <?=basename($_SERVER['PHP_SELF']) == 'specialties.php' ? 'active' : ''?>" href="specialties.php"><i class="bi bi-list-ul"></i> التخصصات الطبية</a>
    <a class="nav-link <?=basename($_SERVER['PHP_SELF']) == 'drugs.php' ? 'active' : ''?>" href="drugs.php"><i class="bi bi-capsule"></i> الأدوية</a>
<a class="nav-link <?=basename($_SERVER['PHP_SELF']) == 'rare_drugs.php' ? 'active' : ''?>" href="rare_drugs.php"><i class="bi bi-exclamation-circle"></i> الأدوية النادرة</a>
  <div class="sidebar-footer">
    <a href="../auth/logout.php" class="sidebar-logout"><i class="bi bi-box-arrow-right"></i> خروج</a>
  </div>
</nav>
<script>
const sidebar = document.getElementById('adminSidebar');
const toggleBtn = document.getElementById('sidebarToggleBtn');
toggleBtn && toggleBtn.addEventListener('click', function(){
  sidebar.classList.toggle('show');
  document.body.classList.toggle('sidebar-open', sidebar.classList.contains('show'));
});
sidebar && sidebar.addEventListener('click', function(e){
  if(e.target.closest('.nav-link') && sidebar.classList.contains('show')) {
    sidebar.classList.remove('show');
    document.body.classList.remove('sidebar-open');
  }
});
</script>