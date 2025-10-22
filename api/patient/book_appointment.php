<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'patient') { header("Location: ../auth/login.php"); exit; }
require_once '../db.php';

// التحقق من الاشتراك وعدد الحجوزات المتبقية
$stmt = $pdo->prepare("SELECT * FROM subscriptions WHERE user_id=? AND status='active' AND start_date<=CURDATE() AND end_date>=CURDATE() ORDER BY id DESC LIMIT 1");
$stmt->execute([$_SESSION['user_id']]);
$sub = $stmt->fetch();
if (!$sub || $sub['used_appointments'] >= $sub['allowed_appointments']) {
    echo '<div class="alert alert-danger text-center my-4">لقد استنفدت كل الحجوزات المسموحة في اشتراكك الحالي. يرجى التجديد أو الترقية.</div>';
    include '../includes/footer.php'; exit;
}

$error = '';
$success = '';
$doctors = $pdo->query("SELECT d.id, u.name, d.photo, s.name as specialty 
    FROM doctors d 
    JOIN users u ON d.user_id = u.id 
    LEFT JOIN specialties s ON d.specialty_id = s.id 
    ORDER BY u.name ASC")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $doctor_id = intval($_POST['doctor_id']);
    $date = $_POST['date'];
    if (!$doctor_id || !$date) {
        $error = "يرجى اختيار الطبيب والتاريخ";
    } else {
        $stmt = $pdo->prepare("SELECT u.name, d.user_id as doctor_user_id FROM doctors d JOIN users u ON d.user_id = u.id WHERE d.id = ?");
        $stmt->execute([$doctor_id]);
        $doctor = $stmt->fetch();
        $doctor_name = $doctor ? $doctor['name'] : '---';
        $doctor_user_id = $doctor ? $doctor['doctor_user_id'] : null;

        $pdo->prepare("INSERT INTO appointments (patient_id, doctor_id, date, status) VALUES (?, ?, ?, 'pending')")
            ->execute([$_SESSION['user_id'], $doctor_id, $date]);
        $appointment_id = $pdo->lastInsertId();

        // خصم ميزة الحجز
        $pdo->prepare("UPDATE subscriptions SET used_appointments = used_appointments + 1 WHERE id=?")->execute([$sub['id']]);

        // إشعار للطبيب
        if ($doctor_user_id) {
            require_once '../functions/notifications.php';
            add_notification($pdo, $doctor_user_id, 'appointment_request', $appointment_id, "لديك طلب موعد جديد من المريض.");
        }

        $success = "تم إرسال طلب الحجز للطبيب <b>$doctor_name</b>، سيتم التواصل معك قريباً.";
    }
}
?>
<?php include '../includes/header.php'; ?>
<div class="container py-5">
    <a href="index.php" class="btn btn-outline-secondary mb-3"><i class="bi bi-arrow-right"></i> رجوع</a>
    <div class="card shadow p-4 mb-4">
        <h3 class="mb-4 text-success text-center"><i class="bi bi-calendar-event"></i> حجز موعد جديد</h3>
        <?php if($error): ?><div class="alert alert-danger"><?=$error?></div><?php endif; ?>
        <?php if($success): ?><div class="alert alert-success"><?=$success?></div><?php endif; ?>
        <form method="post">
            <div class="row mb-3">
                <?php foreach($doctors as $d): ?>
                <div class="col-md-4 mb-3">
                    <label class="card shadow-sm p-3 h-100 text-center doctor-choice" style="cursor:pointer">
                        <input type="radio" name="doctor_id" value="<?=$d['id']?>" required style="display:none;">
                        <img src="<?=($d['photo'] ? '../uploads/'.htmlspecialchars($d['photo']) : '../assets/img/doctor.png')?>"
                             style="width:70px;height:70px;object-fit:cover;border-radius:50%;border:2px solid #36c66f;" class="mb-2">
                        <div class="fw-bold doctor-name"><?=htmlspecialchars($d['name'])?></div>
                        <div class="text-success small doctor-spec"><?=htmlspecialchars($d['specialty'])?></div>
                    </label>
                </div>
                <?php endforeach; ?>
            </div>
            <div class="row mb-3">
                <div class="col-md-6 mx-auto">
                    <label class="form-label">تاريخ الموعد</label>
                    <input type="date" name="date" class="form-control" min="<?=date('Y-m-d')?>" required>
                </div>
            </div>
            <div id="chosen_doctor" class="alert alert-info text-center d-none mb-3"></div>
            <div class="text-center">
                <button type="submit" class="btn btn-success px-5"><i class="bi bi-calendar-check"></i> طلب موعد</button>
            </div>
        </form>
    </div>
</div>
<script>
document.querySelectorAll('input[name="doctor_id"]').forEach(function(radio) {
    radio.addEventListener('change', function() {
        let label = this.closest('label');
        let docName = label.querySelector('.doctor-name').textContent;
        let docSpec = label.querySelector('.doctor-spec') ? label.querySelector('.doctor-spec').textContent : '';
        document.getElementById('chosen_doctor').classList.remove('d-none');
        document.getElementById('chosen_doctor').innerHTML =
            'لقد اخترت الطبيب: <b>' + docName + '</b> <span class="text-success">' + docSpec + '</span>';
    });
});
</script>
<?php include '../includes/footer.php'; ?>