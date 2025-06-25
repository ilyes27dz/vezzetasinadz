<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'patient') { header("Location: ../auth/login.php"); exit; }
require_once '../db.php';

// تحقق من الاشتراك وعدد الاستشارات المتبقية
$stmt = $pdo->prepare("SELECT * FROM subscriptions WHERE user_id=? AND status='active' AND start_date<=CURDATE() AND end_date>=CURDATE() ORDER BY id DESC LIMIT 1");
$stmt->execute([$_SESSION['user_id']]);
$sub = $stmt->fetch();
if (!$sub || $sub['used_consultations'] >= $sub['allowed_consultations']) {
    echo '<div class="alert alert-danger text-center my-4">لقد استنفدت كل الاستشارات المسموحة في اشتراكك الحالي. يرجى التجديد أو الترقية.</div>';
    include '../includes/footer.php'; exit;
}

// جلب الأطباء
$doctors = $pdo->query("SELECT d.id, u.name FROM doctors d JOIN users u ON d.user_id=u.id ORDER BY u.name")->fetchAll();
$error = '';
$success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $doctor_id = intval($_POST['doctor_id']);
    $message = trim($_POST['message']);
    if (!$doctor_id || strlen($message) < 10) {
        $error = "يرجى اختيار الطبيب وكتابة استشارة مفصلة (10 أحرف على الأقل)";
    } else {
        $pdo->prepare("INSERT INTO consultations (patient_id, doctor_id, message, created_at) VALUES (?, ?, ?, NOW())")
            ->execute([$_SESSION['user_id'], $doctor_id, $message]);
        $consultation_id = $pdo->lastInsertId();

        // خصم ميزة استشارة
        $pdo->prepare("UPDATE subscriptions SET used_consultations = used_consultations + 1 WHERE id=?")->execute([$sub['id']]);

        // إشعار للطبيب
        $stmt = $pdo->prepare("SELECT user_id FROM doctors WHERE id=?");
        $stmt->execute([$doctor_id]);
        $doctor_user_id = $stmt->fetchColumn();
        if ($doctor_user_id) {
            require_once '../functions/notifications.php';
            add_notification($pdo, $doctor_user_id, 'consultation_request', $consultation_id, "لديك استشارة طبية جديدة من مريض.");
        }

        $success = "تم إرسال الاستشارة للطبيب المختار.";
    }
}

// جلب الاستشارات المجاب عنها
$stmt = $pdo->prepare("SELECT c.*, u.name AS doctor_name FROM consultations c 
    JOIN doctors d ON c.doctor_id=d.id 
    JOIN users u ON d.user_id=u.id 
    WHERE c.patient_id=? AND c.reply IS NOT NULL AND c.reply != '' 
    ORDER BY c.created_at DESC");
$stmt->execute([$_SESSION['user_id']]);
$consultations = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<?php include '../includes/header.php'; ?>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
<div class="container py-5">
    <a href="index.php" class="btn btn-outline-secondary mb-3"><i class="bi bi-arrow-right"></i> رجوع</a>
    <div class="card p-4 shadow mb-4">
        <h3 class="mb-4 text-success text-center"><i class="bi bi-chat-dots"></i> استشارة طبية جديدة</h3>
        <?php if($error): ?><div class="alert alert-danger"><?=$error?></div><?php endif; ?>
        <?php if($success): ?><div class="alert alert-success"><?=$success?></div><?php endif; ?>
        <form method="post">
            <div class="mb-3">
                <label class="form-label">اختر الطبيب</label>
                <select name="doctor_id" class="form-select" required>
                    <option value="">-- اختر الطبيب --</option>
                    <?php foreach($doctors as $doc): ?>
                        <option value="<?=$doc['id']?>"><?=$doc['name']?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">نص الاستشارة</label>
                <textarea name="message" class="form-control" rows="5" required minlength="10" placeholder="اكتب استشارتك هنا..."></textarea>
            </div>
            <div class="text-center">
                <button class="btn btn-success px-5" type="submit"><i class="bi bi-send"></i> إرسال الاستشارة</button>
            </div>
        </form>
    </div>
    <!-- الاستشارات المجاب عنها -->
    <div class="card shadow border-primary mb-4">
        <div class="card-header bg-primary text-white fw-bold"><i class="bi bi-chat-left-text"></i> الاستشارات المجاب عنها</div>
        <div class="card-body">
            <?php foreach($consultations as $c): ?>
                <div class="mb-3 border-bottom pb-2">
                    <span class="fw-bold text-primary"><?= htmlspecialchars($c['doctor_name']) ?>:</span>
                    <span class="small text-muted"><?= htmlspecialchars($c['created_at']) ?></span>
                    <div class="mt-1"><strong>سؤالك:</strong> <?= nl2br(htmlspecialchars($c['message'])) ?></div>
                    <div class="alert alert-success p-2"><strong>رد الطبيب:</strong> <?= nl2br(htmlspecialchars($c['reply'])) ?></div>
                </div>
            <?php endforeach; if(empty($consultations)): ?>
                <div class="text-muted text-center">لا توجد استشارات مجاب عنها بعد.</div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php include '../includes/footer.php'; ?>