<?php
require_once 'db.php';
$success = $error = '';
$drug_id = isset($_GET['drug_id']) ? intval($_GET['drug_id']) : 0;
$drug = null;
if($drug_id) {
    $stmt = $pdo->prepare("SELECT r.*, s.name AS pharmacy_name FROM rare_drugs r LEFT JOIN services s ON r.pharmacy_id=s.id WHERE r.id=?");
    $stmt->execute([$drug_id]);
    $drug = $stmt->fetch(PDO::FETCH_ASSOC);
}
if(!$drug) die('الدواء غير موجود!');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $phone = trim($_POST['phone'] ?? '');
    $notes = trim($_POST['notes'] ?? '');
    $prescription_file = null;

    // معالجة رفع الملف
    if(isset($_FILES['prescription']) && $_FILES['prescription']['error'] === UPLOAD_ERR_OK){
        $allowed = ['jpg', 'jpeg', 'png', 'pdf'];
        $ext = strtolower(pathinfo($_FILES['prescription']['name'], PATHINFO_EXTENSION));
        if(in_array($ext, $allowed)){
            $dir = 'uploads/rare_prescriptions/';
            if(!is_dir($dir)) mkdir($dir, 0777, true);
            $fname = uniqid('presc_').'.'.$ext;
            move_uploaded_file($_FILES['prescription']['tmp_name'], $dir.$fname);
            $prescription_file = $fname;
        } else {
            $error = "صيغة الملف غير مدعومة (المسموح: jpg, jpeg, png, pdf)";
        }
    }

    if(!$phone) {
        $error = "يرجى إدخال رقم الهاتف";
    } elseif(!$error) {
        $stmt = $pdo->prepare("INSERT INTO rare_drug_requests
            (patient_id, drug_id, pharmacy_id, notes, phone, prescription_file, requested_at, status)
            VALUES (NULL, ?, ?, ?, ?, ?, NOW(), 'جديد')");
        $stmt->execute([
            $drug['id'],
            $drug['pharmacy_id'],
            $notes,
            $phone,
            $prescription_file
        ]);
        $success = "تم إرسال طلبك بنجاح! يمكنك تتبع حالة الطلب باستخدام رقم هاتفك.";
    }
}
?>
<?php include 'includes/header.php'; ?>
<div class="container py-5">
    <a href="drug_search.php" class="btn btn-outline-secondary mb-3"><i class="bi bi-arrow-right"></i> رجوع</a>
    <h2 class="mb-4 text-danger"><i class="bi bi-bag-plus"></i> طلب دواء نادر</h2>
    <?php if($success): ?><div class="alert alert-success"><?= $success ?></div><?php endif; ?>
    <?php if($error): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>
    <div class="card p-4 mb-4">
        <h5>دواء: <b class="text-danger"><?= htmlspecialchars($drug['name']) ?></b></h5>
        <div>الفئة: <?= htmlspecialchars($drug['category']) ?></div>
        <div>الصيدلية: <?= $drug['pharmacy_name'] ?: '<span class="text-danger">غير متوفر بصيدلية</span>' ?></div>
        <?php if($drug['photo']): ?>
            <img src="uploads/rare_drugs/<?=htmlspecialchars($drug['photo'])?>" alt="صورة الدواء" style="width:70px; border-radius:8px; margin:10px 0">
        <?php endif; ?>
    </div>
    <form method="post" enctype="multipart/form-data" class="border p-4 rounded-3 bg-light">
        <div class="mb-3">
            <label>رقم الهاتف</label>
            <input type="text" name="phone" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>الوصفة الطبية (اختياري)</label>
            <input type="file" name="prescription" accept=".jpg,.jpeg,.png,.pdf" class="form-control">
            <div class="small text-muted">يسمح بملف صورة أو PDF فقط.</div>
        </div>
        <div class="mb-3">
            <label>ملاحظات (اختياري)</label>
            <textarea name="notes" class="form-control"></textarea>
        </div>
        <button type="submit" class="btn btn-danger px-4">إرسال الطلب</button>
    </form>
</div>
<?php include 'includes/footer.php'; ?>