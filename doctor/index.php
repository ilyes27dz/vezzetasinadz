<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'doctor') {
    header("Location: ../auth/login.php");
    exit;
}
require_once '../db.php';

$stmt = $pdo->prepare("SELECT id FROM doctors WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$doctor_id = $stmt->fetchColumn();

$consultCount = $pdo->query("SELECT COUNT(*) FROM consultations WHERE doctor_id = $doctor_id")->fetchColumn();
$pendingAppts = $pdo->query("SELECT COUNT(*) FROM appointments WHERE doctor_id = $doctor_id AND status = 'pending'")->fetchColumn();
$prescCount = $pdo->query("SELECT COUNT(*) FROM prescriptions WHERE doctor_id = $doctor_id")->fetchColumn();
?>
<?php include '../includes/header.php'; ?>
<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="text-success"><i class="bi bi-hospital"></i> لوحة تحكم الطبيب</h2>
        <a href="../auth/logout.php" class="btn btn-danger">تسجيل الخروج</a>
    </div>
    <div class="row text-center mb-5">
        <div class="col-md-3">
            <a href="appointments.php" class="card shadow-sm p-4 text-decoration-none text-dark mb-3">
                <i class="bi bi-calendar2-week display-3 text-primary"></i>
                <h5 class="mt-2">إدارة المواعيد</h5>
            </a>
        </div>
        <div class="col-md-3">
            <a href="consultations.php" class="card shadow-sm p-4 text-decoration-none text-dark mb-3">
                <i class="bi bi-chat-dots display-3 text-success"></i>
                <h5 class="mt-2">الاستشارات والردود</h5>
            </a>
        </div>
        <div class="col-md-3">
            <a href="prescriptions.php" class="card shadow-sm p-4 text-decoration-none text-dark mb-3">
                <i class="bi bi-file-earmark-medical display-3 text-info"></i>
                <h5 class="mt-2">الوصفات الطبية</h5>
            </a>
        </div>
        <div class="col-md-3">
            <a href="patients.php" class="card shadow-sm p-4 text-decoration-none text-dark mb-3">
                <i class="bi bi-person-lines-fill display-3 text-warning"></i>
                <h5 class="mt-2">ملفات المرضى</h5>
            </a>
        </div>
        <!-- زر الاشتراك للطبيب -->
        <div class="col-md-3">
            <a href="subscribe.php" class="card shadow-sm p-4 text-decoration-none text-dark mb-3 border-success border-2 bg-light subscribe-doctor-card" style="transition:box-shadow .16s,transform .13s;">
                <i class="bi bi-wallet2 display-3 text-success"></i>
                <h5 class="mt-2">الاشتراك في المنصة</h5>
                <div class="text-muted small">استفد من مزايا إضافية للأطباء</div>
            </a>
        </div>
    </div>
    <div class="row mt-4">
        <div class="col-md-4">
            <div class="card text-center shadow">
                <div class="card-body">
                    <h5 class="card-title">كل الاستشارات</h5>
                    <p class="card-text display-4"><?= $consultCount ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-center shadow">
                <div class="card-body">
                    <h5 class="card-title">مواعيد معلقة</h5>
                    <p class="card-text display-4"><?= $pendingAppts ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-center shadow">
                <div class="card-body">
                    <h5 class="card-title">وصفات طبية</h5>
                    <p class="card-text display-4"><?= $prescCount ?></p>
                </div>
            </div>
        </div>
    </div>
</div>
<style>
/* زر الاشتراك للطبيب */
.subscribe-doctor-card {
    border: 2.5px solid #36c66f66 !important;
    background: linear-gradient(120deg, #f0fff7 70%, #e0f7fa 100%) !important;
    color: #145e2b !important;
    box-shadow: 0 4px 24px #36c66f17;
}
.subscribe-doctor-card:hover {
    box-shadow: 0 12px 32px #0db98d28;
    background: #e6fff4 !important;
    color: #0db98d !important;
    transform: scale(1.03);
}
</style>
<?php include '../includes/footer.php'; ?>