<?php
require_once '../db.php';
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'doctor' || !isset($_GET['id'])) {
    header("Location: ../auth/login.php");
    exit;
}
$id = intval($_GET['id']);
$stmt = $pdo->prepare("SELECT * FROM prescriptions WHERE id = ?");
$stmt->execute([$id]);
$prescription = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$prescription) {
    echo "<div class='alert alert-danger'>الوصفة غير موجودة.</div>";
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $notes = trim($_POST['notes'] ?? '');
    $file_name = $prescription['file'];

    // معالجة رفع الملف الجديد إن وجد
    if (isset($_FILES['presc_file']) && $_FILES['presc_file']['error'] == 0) {
        $ext = strtolower(pathinfo($_FILES['presc_file']['name'], PATHINFO_EXTENSION));
        $allowed = ['pdf','jpg','jpeg','png'];
        if (in_array($ext, $allowed)) {
            $new_name = "presc_".time()."_".rand(100,999).".".$ext;
            move_uploaded_file($_FILES['presc_file']['tmp_name'], "../uploads/".$new_name);
            $file_name = $new_name;
        } else {
            $error = "امتداد الملف غير مسموح. فقط PDF أو صورة.";
        }
    }

    if (!$error) {
        $stmt = $pdo->prepare("UPDATE prescriptions SET notes=?, file=? WHERE id=?");
        $stmt->execute([$notes, $file_name, $id]);

        // جلب patient_id لإرسال الإشعار
        $stmt_patient = $pdo->prepare("SELECT patient_id FROM prescriptions WHERE id=?");
        $stmt_patient->execute([$id]);
        $patient_id = $stmt_patient->fetchColumn();

        if ($patient_id) {
            require_once '../functions/notifications.php';
            add_notification(
                $pdo,
                $patient_id,
                'prescription_updated',
                $id,
                "تم تعديل وصفتك الطبية من قبل الطبيب. يمكنك الآن الإطلاع على التحديث."
            );
        }

        $success = "تم تعديل الوصفة بنجاح، وتم إشعار المريض.";
        header("Location: prescriptions.php?success=1");
        exit;
    }
}
?>

<?php include '../includes/header.php'; ?>
<div class="container py-5">
    <a href="prescriptions.php" class="btn btn-light mb-3"><i class="bi bi-arrow-right"></i> رجوع للوصفات</a>
    <h3 class="mb-4 text-success"><i class="bi bi-pencil-square"></i> تعديل وصفة طبية</h3>
    <?php if($error): ?><div class="alert alert-danger"><?=$error?></div><?php endif; ?>
    <?php if($success): ?><div class="alert alert-success"><?=$success?></div><?php endif; ?>
    <form method="post" enctype="multipart/form-data" class="w-50 mx-auto border p-4 shadow-lg rounded-3 bg-light">
        <label class="mb-2 fw-bold">ملاحظات/تفاصيل الوصفة:</label>
        <textarea name="notes" class="form-control mb-3" rows="5" required><?=htmlspecialchars($prescription['notes'])?></textarea>
        
        <label class="mb-2 fw-bold">ملف الوصفة (اختياري):</label>
        <input type="file" name="presc_file" class="form-control mb-2">
        <?php if(!empty($prescription['file'])): ?>
            <div class="mb-2">
                <a href="../uploads/<?=htmlspecialchars($prescription['file'])?>" target="_blank" class="btn btn-outline-success btn-sm">
                    <i class="bi bi-download"></i> تحميل الملف الحالي
                </a>
            </div>
        <?php endif; ?>
        <button type="submit" class="btn btn-success w-100">حفظ التعديل</button>
    </form>
</div>
<?php include '../includes/footer.php'; ?>