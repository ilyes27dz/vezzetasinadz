<?php
require_once 'db.php';
$success = '';
if ($_SERVER['REQUEST_METHOD']=='POST') {
    $donor_name = trim($_POST['donor_name']);
    $city = trim($_POST['city']);
    $blood_type = trim($_POST['blood_type']);
    $contact = trim($_POST['contact']);
    $details = trim($_POST['details']);
    if($donor_name && $city && $blood_type && $contact) {
        $stmt = $pdo->prepare(
            "INSERT INTO blood_donors (donor_name, city, blood_type, contact, details, is_active, created_at)
             VALUES (?, ?, ?, ?, ?, 0, NOW())"
        );
        $stmt->execute([$donor_name, $city, $blood_type, $contact, $details]);
        $success = "تم إرسال طلبك! سيتم مراجعته من المسؤول قبل ظهوره في العروض.";
        // إشعار للمسؤولين
        $admins = $pdo->query("SELECT id FROM users WHERE role='admin'")->fetchAll(PDO::FETCH_COLUMN);
        foreach($admins as $admin_id) {
            $msg = "يوجد طلب متبرع دم جديد يحتاج للموافقة.";
            $pdo->prepare("INSERT INTO notifications (user_id, message, type, is_read, created_at) VALUES (?, ?, 'blood_donor_request', 0, NOW())")
                ->execute([$admin_id, $msg]);
        }
        $_POST = [];
    }
}
include 'includes/header.php';
?>
<div class="container py-5">
    <div class="mb-4">
        <a href="index.php" class="btn btn-outline-secondary me-2 mb-2"><i class="bi bi-arrow-right"></i> العودة للرئيسية</a>
    </div>
    <h2 class="mb-4 text-danger"><i class="bi bi-droplet-half"></i> أضف نفسك كمتبرع بالدم</h2>
    <?php if($success): ?><div class="alert alert-success"><?=$success?></div><?php endif; ?>
    <form method="post" class="w-50 mx-auto border p-4 shadow-lg rounded-3 bg-light">
        <label class="fw-bold mb-2">اسمك:</label>
        <input type="text" name="donor_name" class="form-control mb-2" required value="<?=htmlspecialchars($_POST['donor_name'] ?? '')?>">
        <label class="fw-bold mb-2">المدينة:</label>
        <input type="text" name="city" class="form-control mb-2" required value="<?=htmlspecialchars($_POST['city'] ?? '')?>">
        <label class="fw-bold mb-2">زمرة الدم:</label>
        <select name="blood_type" class="form-control mb-2" required>
            <option value="">اختر</option>
            <option>O+</option><option>O-</option><option>A+</option><option>A-</option>
            <option>B+</option><option>B-</option><option>AB+</option><option>AB-</option>
        </select>
        <label class="fw-bold mb-2">رقم التواصل (هاتف أو واتساب):</label>
        <input type="text" name="contact" class="form-control mb-2" required value="<?=htmlspecialchars($_POST['contact'] ?? '')?>">
        <label class="fw-bold mb-2">تفاصيل إضافية (اختياري):</label>
        <textarea name="details" class="form-control mb-2"><?=htmlspecialchars($_POST['details'] ?? '')?></textarea>
        <button class="btn btn-danger w-100">حفظ إعلانك</button>
    </form>
</div>
<?php include 'includes/footer.php'; ?>