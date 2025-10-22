<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'patient') {
    header("Location: ../auth/login.php"); exit;
}
require_once '../db.php';

$success = '';
$error = '';

// جلب قائمة الأدوية النادرة المتوفرة في صيدلية واحدة على الأقل
$rare_drugs = $pdo->query(
    "SELECT r.*, s.name AS pharmacy_name
     FROM rare_drugs r
     INNER JOIN services s ON r.pharmacy_id = s.id
     ORDER BY r.name"
)->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $drug_id = intval($_POST['drug_id']);
    $phone = trim($_POST['phone']);
    $notes = trim($_POST['notes']);
    $prescription_file = null;

    // جلب بيانات الدواء لمعرفة الصيدلية المالكة
    $stmt = $pdo->prepare("SELECT pharmacy_id FROM rare_drugs WHERE id=?");
    $stmt->execute([$drug_id]);
    $pharmacy_id = $stmt->fetchColumn();

    // تحقق من صحة البيانات
    if (!$drug_id || !$pharmacy_id) {
        $error = "يرجى اختيار الدواء من القائمة.";
    } elseif (!$phone || !preg_match('/^0[567]\d{8}$/', $phone)) {
        $error = "يرجى إدخال رقم هاتف صحيح (يبدأ بـ 05 أو 06 أو 07 و10 أرقام)";
    }

    // رفع الوصفة الطبية
    if (!$error) {
        if (!empty($_FILES['prescription_file']['name'])) {
            $ext = strtolower(pathinfo($_FILES['prescription_file']['name'], PATHINFO_EXTENSION));
            $allowed = ['jpg','jpeg','png','pdf'];
            if (!in_array($ext, $allowed)) {
                $error = "صيغة الملف غير مدعومة!";
            } else {
                $fname = uniqid('rare_presc_').'.'.$ext;
                if (!is_dir("../rare_prescriptions")) mkdir("../rare_prescriptions", 0777, true);
                if (move_uploaded_file($_FILES['prescription_file']['tmp_name'], "../rare_prescriptions/$fname")) {
                    $prescription_file = $fname;
                } else {
                    $error = 'فشل رفع الوصفة الطبية!';
                }
            }
        } else {
            $error = "يرجى رفع وصفة طبية (صورة أو PDF)";
        }
    }

    // حفظ الطلب
    if (!$error) {
        $stmt = $pdo->prepare("INSERT INTO rare_drug_requests (patient_id, drug_id, pharmacy_id, notes, phone, prescription_file, requested_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
        $stmt->execute([$_SESSION['user_id'], $drug_id, $pharmacy_id, $notes, $phone, $prescription_file]);
        $success = "تم إرسال طلبك بنجاح! سيتم التواصل معك من الصيدلية.";
    }
}
?>
<?php include '../includes/header.php'; ?>
<div class="container py-5">
    <a href="rare_drugs.php" class="btn btn-outline-secondary mb-3"><i class="bi bi-arrow-right"></i> رجوع</a>
    <div class="card p-4 shadow mb-4">
        <h3 class="mb-4 text-danger text-center"><i class="bi bi-exclamation-circle"></i> طلب دواء نادر</h3>
        <?php if($error): ?><div class="alert alert-danger"><?=$error?></div><?php endif; ?>
        <?php if($success): ?><div class="alert alert-success"><?=$success?></div><?php endif; ?>
        <form method="post" enctype="multipart/form-data" autocomplete="off">
            <div class="mb-3">
                <label class="form-label">اختر الدواء النادر</label>
                <select name="drug_id" class="form-select" required>
                    <option value="">-- اختر الدواء --</option>
                    <?php foreach($rare_drugs as $drug): ?>
                        <option value="<?=$drug['id']?>">
                            <?= htmlspecialchars($drug['name']) ?> <?= $drug['category'] ? "({$drug['category']})" : "" ?> - <?= htmlspecialchars($drug['pharmacy_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">رقم الهاتف للتواصل</label>
                <input type="text" name="phone" class="form-control" required pattern="^0[567]\d{8}$" placeholder="06xxxxxxxx">
            </div>
            <div class="mb-3">
                <label class="form-label">ملاحظات إضافية (اختياري)</label>
                <textarea name="notes" class="form-control" rows="2"></textarea>
            </div>
            <div class="mb-3">
                <label class="form-label">ارفع وصفة طبية (PDF, صورة)</label>
                <input type="file" name="prescription_file" class="form-control" accept="image/*,.pdf" required>
            </div>
            <div class="text-center">
                <button class="btn btn-danger px-5" type="submit"><i class="bi bi-send"></i> إرسال الطلب</button>
            </div>
        </form>
    </div>
</div>
<?php include '../includes/footer.php'; ?>