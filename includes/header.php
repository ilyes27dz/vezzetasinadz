<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <title>VEZEETA SINA DZ</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Bootstrap CSS & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/ayloul_project/assets/css/style.css">
    <!-- أيقونة الموقع -->
    <link rel="icon" type="image/png" href="assets/img/LOGO.svg">
    <style>
        body { font-family: 'Cairo', Arial, sans-serif; }
        .navbar .dropdown-menu { max-height: 400px; overflow-y: auto; z-index: 9999; }
        .dropdown-menu { z-index: 9999 !important; }
        .show > .dropdown-menu, .dropdown-menu.show { display: block; }
        .navbar-brand-logo {
            width: 38px;
            height: 38px;
            object-fit: contain;
            margin-left: 7px;
            vertical-align: middle;
        }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-light bg-white fixed-top py-3 shadow-sm">
  <div class="container">
    <a class="navbar-brand text-success fw-bold d-flex align-items-center" href="/ayloul_project/index.php">
      <img src="/ayloul_project/assets/img/LOGO.svg" alt="شعار فيزيتا" class="navbar-brand-logo">
      <span>فيزيتا سينا ديزاد</span>
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbar">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse justify-content-end" id="mainNavbar">
      <ul class="navbar-nav mb-2 mb-lg-0 gap-2 align-items-center">
        <?php
          if (session_status() === PHP_SESSION_NONE) session_start();
          require_once __DIR__ . '/../db.php';
          $role = $_SESSION['role'] ?? '';
          if ($role !== 'patient' && $role !== 'doctor' && $role !== 'pharmacy'){
        ?>
          <li class="nav-item"><a class="nav-link" href="/ayloul_project/index.php">الرئيسية</a></li>
        <?php
          }
          // جرس الإشعارات (تأكد أن ملف notifications_menu.php محدث كما أرسلته لك)
          if (isset($_SESSION['user_id'])) {
            include __DIR__ . '/../functions/notifications_menu.php';
          }
          if (!isset($_SESSION['user_id'])):
        ?>
            <li class="nav-item"><a class="nav-link" href="/ayloul_project/auth/login.php">تسجيل الدخول</a></li>
            <li class="nav-item"><a class="nav-link" href="/ayloul_project/auth/register.php">تسجيل جديد</a></li>
        <?php
          else:
            if ($role == 'admin'):
        ?>
              <li class="nav-item"><a class="nav-link" href="/ayloul_project/admin/dashboard.php">لوحة الإدارة</a></li>
              <li class="nav-item"><a class="nav-link" href="/ayloul_project/admin/profile.php">الملف الشخصي</a></li>
              <li class="nav-item"><a class="nav-link text-danger" href="/ayloul_project/admin/logout.php">تسجيل الخروج</a></li>
        <?php
            elseif ($role == 'pharmacy'):
        ?>
              <li class="nav-item"><a class="nav-link" href="/ayloul_project/pharmacy/dashboard.php">لوحة الصيدلية</a></li>
              <li class="nav-item"><a class="nav-link" href="/ayloul_project/pharmacy/profile.php">الملف الشخصي</a></li>
              <li class="nav-item"><a class="nav-link text-danger" href="/ayloul_project/pharmacy/logout.php">تسجيل الخروج</a></li>
        <?php
            elseif ($role == 'doctor'):
        ?>
              <li class="nav-item"><a class="nav-link" href="/ayloul_project/doctor/index.php">لوحة الطبيب</a></li>
              <li class="nav-item"><a class="nav-link" href="/ayloul_project/doctor/profile.php">الملف الشخصي</a></li>
              <li class="nav-item"><a class="nav-link text-danger" href="/ayloul_project/pharmacy/logout.php">تسجيل الخروج</a></li>
        <?php
            elseif ($role == 'patient'):
        ?>
              <li class="nav-item"><a class="nav-link" href="/ayloul_project/patient/index.php">لوحة المريض</a></li>
              <li class="nav-item"><a class="nav-link" href="/ayloul_project/patient/profile.php">الملف الشخصي</a></li>
              <li class="nav-item"><a class="nav-link text-danger" href="/ayloul_project/pharmacy/logout.php">تسجيل الخروج</a></li>
        <?php
            endif;
          endif;
        ?>
      </ul>
    </div>
  </div>
</nav>
<div style="height:72px"></div>
<!-- Bootstrap JS: لا تكرر هذا السطر في أي مكان آخر -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>