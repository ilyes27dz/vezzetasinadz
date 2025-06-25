<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php"); exit;
}
require_once '../db.php';
$counts = [
  'doctors'   => $pdo->query("SELECT COUNT(*) FROM doctors")->fetchColumn(),
  'patients'  => $pdo->query("SELECT COUNT(*) FROM users WHERE role='patient'")->fetchColumn(),
  'pharmacies'=> $pdo->query("SELECT COUNT(*) FROM users WHERE role='pharmacy'")->fetchColumn(),
  'appointments'=> $pdo->query("SELECT COUNT(*) FROM appointments")->fetchColumn(),
  'testimonials_pending'=> $pdo->query("SELECT COUNT(*) FROM testimonials WHERE approved=0")->fetchColumn(),
  'ads'       => $pdo->query("SELECT COUNT(*) FROM medical_ads")->fetchColumn(),
  'blood'     => $pdo->query("SELECT COUNT(*) FROM blood_requests WHERE is_active=1")->fetchColumn(),
  'distributors' => $pdo->query("SELECT COUNT(*) FROM distributors")->fetchColumn(),
  'subscriptions' => $pdo->query("SELECT COUNT(*) FROM subscriptions")->fetchColumn(),
  'rare_drugs' => $pdo->query("SELECT COUNT(*) FROM rare_drugs")->fetchColumn(),
];
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8">
  <title>لوحة تحكم المسؤول</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <style>
    body {background: #f7fcff;}
    .main-content-admin {
      margin-right: 240px;
      padding: 90px 36px 16px 16px;
      min-height: 100vh;
      transition: background 0.2s;
    }
    @media (max-width:991px){
      .main-content-admin { margin-right: 0; padding: 70px 6px 10px 6px;}
    }
    .dash-section-title {
      font-size: 1.22rem;
      color: #0db98d;
      font-weight: bold;
      margin-bottom: 15px;
      letter-spacing: .01em;
    }
    .actions-section-title {
      font-size: 1.12rem;
      color: #1d6a56;
      font-weight: bold;
      margin: 32px 0 15px 0;
      letter-spacing: .01em;
    }
    .stat-card {
      background: #fff;
      border: 2px solid #eafaf6;
      border-radius: 13px;
      transition: box-shadow .12s,border-color .14s;
      box-shadow: 0 2px 10px #0db98d12;
      cursor: default;
    }
    .stat-card .fs-1 {font-size:2.2rem !important;}
    .stat-card:hover {box-shadow: 0 8px 28px #0db98d1a; border-color: #0db98d;}
    .main-content-admin .card a, .main-content-admin .stat-card a {pointer-events:none;}
    .action-btn-card {
      cursor: pointer !important;
      border: 2px solid #e1f5f8;
      border-radius: 15px;
      background: #fff;
      transition: box-shadow .11s,border-color .13s;
      box-shadow: 0 2px 10px #14e49c12;
      text-decoration: none !important;
      color: #0db98d !important;
      font-weight: bold;
      min-height: 170px;
      display: flex; flex-direction: column; align-items: center; justify-content: center;
    }
    .action-btn-card:hover {box-shadow: 0 8px 28px #14e49c21; border-color: #0db98d;}
    .action-btn-card .action-ico {
      font-size: 2.8rem;
      margin-bottom: 8px;
      display: block;
      text-align: center;
    }
  </style>
</head>
<body>
<?php include 'includes/navbar.php'; ?>
<?php include 'includes/sidebar.php'; ?>

<div class="main-content-admin">
  <div class="container py-4">
    <h2 class="mb-4 fw-bold text-success"><i class="bi bi-grid-1x2"></i> لوحة تحكم المسؤول</h2>
    
    <div class="dash-section-title">إحصائيات عامة</div>
    <div class="row g-4 mb-4">
      <div class="col-md-2 col-6">
        <div class="stat-card text-center shadow border-success border-2 py-3">
          <i class="bi bi-person-badge fs-1 text-success"></i>
          <div class="fw-bold fs-4"><?= $counts['doctors'] ?></div>
          <div>الأطباء</div>
        </div>
      </div>
      <div class="col-md-2 col-6">
        <div class="stat-card text-center shadow border-info border-2 py-3">
          <i class="bi bi-people fs-1 text-info"></i>
          <div class="fw-bold fs-4"><?= $counts['patients'] ?></div>
          <div>المرضى</div>
        </div>
      </div>
      <div class="col-md-2 col-6">
        <div class="stat-card text-center shadow border-primary border-2 py-3">
          <i class="bi bi-capsule fs-1 text-primary"></i>
          <div class="fw-bold fs-4"><?= $counts['pharmacies'] ?></div>
          <div>الصيدليات</div>
        </div>
      </div>
      <div class="col-md-2 col-6">
        <div class="stat-card text-center shadow border-warning border-2 py-3">
          <i class="bi bi-calendar-check fs-1 text-warning"></i>
          <div class="fw-bold fs-4"><?= $counts['appointments'] ?></div>
          <div>المواعيد</div>
        </div>
      </div>
      <div class="col-md-2 col-6">
        <div class="stat-card text-center shadow border-danger border-2 py-3">
          <i class="bi bi-chat-dots fs-1 text-danger"></i>
          <div class="fw-bold fs-4"><?= $counts['testimonials_pending'] ?></div>
          <div>تعليقات تنتظر الموافقة</div>
        </div>
      </div>
      <div class="col-md-2 col-6">
        <div class="stat-card text-center shadow border-secondary border-2 py-3">
          <i class="bi bi-megaphone fs-1 text-secondary"></i>
          <div class="fw-bold fs-4"><?= $counts['ads'] ?></div>
          <div>الإعلانات</div>
        </div>
      </div>
      <div class="col-md-2 col-6">
        <div class="stat-card text-center shadow border-dark border-2 py-3">
          <i class="bi bi-truck fs-1 text-dark"></i>
          <div class="fw-bold fs-4"><?= $counts['distributors'] ?></div>
          <div>الموزعون</div>
        </div>
      </div>
      <div class="col-md-2 col-6">
        <div class="stat-card text-center shadow border-info border-2 py-3">
          <i class="bi bi-wallet2 fs-1 text-info"></i>
          <div class="fw-bold fs-4"><?= $counts['subscriptions'] ?></div>
          <div>الاشتراكات</div>
        </div>
      </div>
      <div class="col-md-2 col-6">
        <div class="stat-card text-center shadow border-danger border-2 py-3">
          <i class="bi bi-exclamation-circle fs-1 text-danger"></i>
          <div class="fw-bold fs-4"><?= $counts['rare_drugs'] ?></div>
          <div>الأدوية النادرة</div>
        </div>
      </div>
    </div>

    <div class="actions-section-title">إجراءات الإدارة</div>
    <div class="row g-4">
      <div class="col-6 col-md-4 col-lg-3">
        <a href="doctors.php" class="action-btn-card card text-center p-3 shadow h-100">
          <span class="action-ico text-success"><i class="bi bi-person-badge"></i></span>
          <h5 class="mt-2 fw-bold">إدارة الأطباء</h5>
        </a>
      </div>
      <div class="col-6 col-md-4 col-lg-3">
        <a href="appointments.php" class="action-btn-card card text-center p-3 shadow h-100">
          <span class="action-ico text-warning"><i class="bi bi-calendar-check"></i></span>
          <h5 class="mt-2 fw-bold">إدارة المواعيد</h5>
        </a>
      </div>
      <div class="col-6 col-md-4 col-lg-3">
        <a href="testimonials.php" class="action-btn-card card text-center p-3 shadow h-100">
          <span class="action-ico text-danger"><i class="bi bi-chat-dots"></i></span>
          <h5 class="mt-2 fw-bold">مراجعة التعليقات</h5>
        </a>
      </div>
      <div class="col-6 col-md-4 col-lg-3">
        <a href="ads.php" class="action-btn-card card text-center p-3 shadow h-100">
          <span class="action-ico text-secondary"><i class="bi bi-megaphone"></i></span>
          <h5 class="mt-2 fw-bold">إدارة الإعلانات</h5>
        </a>
      </div>
      <div class="col-6 col-md-4 col-lg-3">
        <a href="blood_requests.php" class="action-btn-card card text-center p-3 shadow h-100">
          <span class="action-ico text-danger"><i class="bi bi-droplet-half"></i></span>
          <h5 class="mt-2 fw-bold">طلبات التبرع بالدم</h5>
        </a>
      </div>
      <div class="col-6 col-md-4 col-lg-3">
        <a href="blood_donors.php" class="action-btn-card card text-center p-3 shadow h-100">
          <span class="action-ico text-danger"><i class="bi bi-droplet"></i></span>
          <h5 class="mt-2 fw-bold">متبرعو الدم</h5>
        </a>
      </div>
      <div class="col-6 col-md-4 col-lg-3">
        <a href="services.php" class="action-btn-card card text-center p-3 shadow h-100">
          <span class="action-ico text-primary"><i class="bi bi-list-ul"></i></span>
          <h5 class="mt-2 fw-bold">إدارة الخدمات</h5>
        </a>
      </div>
      <div class="col-6 col-md-4 col-lg-3">
        <a href="distributors.php" class="action-btn-card card text-center p-3 shadow h-100">
          <span class="action-ico text-dark"><i class="bi bi-truck"></i></span>
          <h5 class="mt-2 fw-bold">إدارة الموزعين</h5>
        </a>
      </div>
      <div class="col-6 col-md-4 col-lg-3">
        <a href="add_pharmacy_user.php" class="action-btn-card card text-center p-3 shadow h-100">
          <span class="action-ico text-info"><i class="bi bi-capsule-pill"></i></span>
          <h5 class="mt-2 fw-bold">إضافة صيدلية</h5>
        </a>
      </div>
      <div class="col-6 col-md-4 col-lg-3">
        <a href="subscriptions.php" class="action-btn-card card text-center p-3 shadow h-100">
          <span class="action-ico text-info"><i class="bi bi-wallet2"></i></span>
          <h5 class="mt-2 fw-bold">الاشتراكات</h5>
        </a>
      </div>
      <div class="col-6 col-md-4 col-lg-3">
        <a href="messages.php" class="action-btn-card card text-center p-3 shadow h-100">
          <span class="action-ico text-primary"><i class="bi bi-envelope"></i></span>
          <h5 class="mt-2 fw-bold">الرسائل الواردة</h5>
        </a>
      </div>
      <div class="col-6 col-md-4 col-lg-3">
        <a href="rare_drugs.php" class="action-btn-card card text-center p-3 shadow h-100">
          <span class="action-ico text-danger"><i class="bi bi-exclamation-circle"></i></span>
          <h5 class="mt-2 fw-bold">الأدوية النادرة</h5>
        </a>
      </div>
    </div>
    <div class="text-center mt-4">
      <small class="text-muted">جميع الحقوق محفوظة &copy; <?=date('Y')?> - منصة أيلول الطبية</small>
    </div>
  </div>
</div>
</body>
</html>