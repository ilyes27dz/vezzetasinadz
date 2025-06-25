<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'pharmacy') { header("Location: ../auth/login.php"); exit; }
?>
<?php include '../includes/header.php'; ?>
<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="text-success mb-0"><i class="bi bi-capsule"></i> لوحة تحكم الصيدلية</h3>
        <a href="logout.php" class="btn btn-outline-danger"><i class="bi bi-box-arrow-right"></i> تسجيل الخروج</a>
    </div>
    <div class="row g-4">
        <div class="col-md-4">
            <a href="drugs.php" class="card shadow text-decoration-none text-dark h-100">
                <div class="card-body text-center">
                    <i class="bi bi-list-ul display-4 text-primary"></i>
                    <h5 class="mt-3 mb-1">إدارة الأدوية والأسعار</h5>
                    <p class="text-muted mb-0">إضافة، تعديل أو حذف الأدوية</p>
                </div>
            </a>
        </div>
        <div class="col-md-4">
            <a href="orders.php" class="card shadow text-decoration-none text-dark h-100">
                <div class="card-body text-center">
                    <i class="bi bi-truck display-4 text-success"></i>
                    <h5 class="mt-3 mb-1">تتبع الطلبيات</h5>
                    <p class="text-muted mb-0">مراقبة وإدارة حالات الطلبات</p>
                </div>
            </a>
        </div>
        <div class="col-md-4">
            <a href="rare_orders.php" class="card shadow text-decoration-none text-danger h-100">
                <div class="card-body text-center">
                    <i class="bi bi-exclamation-circle display-4 text-danger"></i>
                    <h5 class="mt-3 mb-1">طلبات الأدوية النادرة</h5>
                    <p class="text-muted mb-0">عرض ومتابعة طلبات الأدوية النادرة</p>
                </div>
            </a>
        </div>
        <div class="col-md-4">
            <a href="profile.php" class="card shadow text-decoration-none text-dark h-100">
                <div class="card-body text-center">
                    <i class="bi bi-person-circle display-4 text-info"></i>
                    <h5 class="mt-3 mb-1">الملف الشخصي</h5>
                    <p class="text-muted mb-0">تعديل بيانات الصيدلية</p>
                </div>
            </a>
        </div>
    </div>
</div>
<?php include '../includes/footer.php'; ?>