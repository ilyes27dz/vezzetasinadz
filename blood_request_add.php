<?php
session_start();
require_once 'db.php';
$success = '';
if ($_SERVER['REQUEST_METHOD']=='POST') {
    $patient_name = trim($_POST['patient_name']);
    $city = trim($_POST['city']);
    $blood_type = trim($_POST['blood_type']);
    $urgency = $_POST['urgency'];
    $contact = trim($_POST['contact']);
    $details = trim($_POST['details']);
    if($patient_name && $city && $blood_type && $urgency && $contact) {
        $stmt = $pdo->prepare(
            "INSERT INTO blood_requests (patient_name, city, blood_type, urgency, contact, details, is_active, created_at) VALUES (?, ?, ?, ?, ?, ?, 0, NOW())"
        );
        $stmt->execute([$patient_name, $city, $blood_type, $urgency, $contact, $details]);
        // إشعار للمسؤولين
        $admins = $pdo->query("SELECT id FROM users WHERE role='admin'")->fetchAll(PDO::FETCH_COLUMN);
        foreach($admins as $admin_id) {
            $msg = "يوجد طلب تبرع دم جديد يحتاج للموافقة.";
            $pdo->prepare("INSERT INTO notifications (user_id, message, type, is_read, created_at) VALUES (?, ?, 'blood_request', 0, NOW())")
                ->execute([$admin_id, $msg]);
        }
        $success = "تم إرسال طلبك بنجاح! سيتم مراجعته من المسؤول قبل ظهوره للزوار.";
        $_POST = [];
    } else {
        $success = "يرجى ملء جميع الحقول المطلوبة.";
    }
}
include 'includes/header.php';
?>
<div class="container py-5">
    <div class="mb-4">
        <a href="index.php" class="btn btn-outline-secondary me-2 mb-2"><i class="bi bi-arrow-right"></i> العودة للرئيسية</a>
        <a href="services/list.php?type=doctor" class="btn btn-outline-success me-2 mb-2">كل الخدمات</a>
        <a href="contact.php" class="btn btn-outline-info mb-2">تواصل معنا</a>
    </div>
    <h2 class="mb-4 text-danger"><i class="bi bi-droplet-half"></i> إضافة طلب تبرع بالدم</h2>
    <?php if($success): ?><div class="alert alert-info"><?=$success?></div><?php endif; ?>
    <form method="post" class="w-50 mx-auto border p-4 shadow-lg rounded-3 bg-light">
        <label class="fw-bold mb-2">اسم المريض:</label>
        <input type="text" name="patient_name" class="form-control mb-2" required value="<?=htmlspecialchars($_POST['patient_name'] ?? '')?>">
        <label class="fw-bold mb-2">المدينة:</label>
        <input type="text" name="city" class="form-control mb-2" required value="<?=htmlspecialchars($_POST['city'] ?? '')?>">
        <label class="fw-bold mb-2">زمرة الدم:</label>
        <select name="blood_type" class="form-control mb-2" required>
            <option value="">اختر</option>
            <option>O+</option><option>O-</option><option>A+</option><option>A-</option>
            <option>B+</option><option>B-</option><option>AB+</option><option>AB-</option>
        </select>
        <label class="fw-bold mb-2">حالة الأهمية:</label>
        <select name="urgency" class="form-control mb-2" required>
            <option value="حرجة">حرجة</option>
            <option value="مستعجلة">مستعجلة</option>
            <option value="عادية">عادية</option>
        </select>
        <label class="fw-bold mb-2">رقم التواصل أو وسيلة:</label>
        <input type="text" name="contact" class="form-control mb-2" required value="<?=htmlspecialchars($_POST['contact'] ?? '')?>">
        <label class="fw-bold mb-2">تفاصيل أخرى:</label>
        <textarea name="details" class="form-control mb-2"><?=htmlspecialchars($_POST['details'] ?? '')?></textarea>
        <button class="btn btn-danger w-100">حفظ الطلب</button>
    </form>
</div>
<?php include 'includes/footer.php'; ?>