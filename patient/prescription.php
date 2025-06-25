<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'patient') { header("Location: ../auth/login.php"); exit; }
require_once '../db.php';

// تحقق من الاشتراك وعدد الوصفات المتبقية
$stmt = $pdo->prepare("SELECT * FROM subscriptions WHERE user_id=? AND status='active' AND start_date<=CURDATE() AND end_date>=CURDATE() ORDER BY id DESC LIMIT 1");
$stmt->execute([$_SESSION['user_id']]);
$sub = $stmt->fetch();
if (!$sub || $sub['used_prescriptions'] >= $sub['allowed_prescriptions']) {
    echo '<div class="alert alert-danger text-center my-4">لقد استنفدت كل طلبات الوصفات المسموحة في اشتراكك الحالي. يرجى التجديد أو الترقية.</div>';
    include '../includes/footer.php'; exit;
}

$success = '';
$error = '';

// جلب الأطباء
$doctors = $pdo->query("SELECT d.id, u.name FROM doctors d JOIN users u ON d.user_id=u.id ORDER BY u.name")->fetchAll();

// معالجة زر تفعيل/إيقاف التجديد التلقائي مع إرسال إشعار للطبيب
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_auto_renew_id'])) {
    $presc_id = intval($_POST['toggle_auto_renew_id']);
    // جلب الوصفة والتأكد من ملكيتها
    $stmt = $pdo->prepare("SELECT auto_renew, doctor_id FROM prescriptions WHERE id=? AND patient_id=?");
    $stmt->execute([$presc_id, $_SESSION['user_id']]);
    $presc = $stmt->fetch();
    if ($presc) {
        $new_status = empty($presc['auto_renew']) ? 1 : 0;
        $pdo->prepare("UPDATE prescriptions SET auto_renew=? WHERE id=?")
            ->execute([$new_status, $presc_id]);
        // إشعار للطبيب عند التفعيل أو الإيقاف
        $stmt_doctor = $pdo->prepare("SELECT user_id FROM doctors WHERE id=?");
        $stmt_doctor->execute([$presc['doctor_id']]);
        $doctor_user_id = $stmt_doctor->fetchColumn();
        if ($doctor_user_id) {
            require_once '../functions/notifications.php';
            if ($new_status) {
                add_notification($pdo, $doctor_user_id, 'auto_renew_enabled', $presc_id, "قام المريض بتفعيل التجديد التلقائي لوصفة رقم #$presc_id.");
            } else {
                add_notification($pdo, $doctor_user_id, 'auto_renew_disabled', $presc_id, "قام المريض بإيقاف التجديد التلقائي لوصفة رقم #$presc_id.");
            }
        }
        $success = $new_status ? "تم تفعيل التجديد التلقائي بنجاح." : "تم إيقاف التجديد التلقائي بنجاح.";
    }
}

// إضافة وصفة جديدة
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['doctor_id']) && !isset($_POST['renew_prescription_id'])) {
    $doctor_id = intval($_POST['doctor_id']);
    $notes = trim($_POST['notes']);
    $auto_renew = isset($_POST['auto_renew']) ? 1 : 0;
    if (!$doctor_id) {
        $error = "يرجى اختيار الطبيب";
    } else {
        $pdo->prepare("INSERT INTO prescriptions (patient_id, doctor_id, notes, created_at, auto_renew) VALUES (?, ?, ?, NOW(), ?)")
            ->execute([$_SESSION['user_id'], $doctor_id, $notes, $auto_renew]);
        $prescription_id = $pdo->lastInsertId();

        // خصم ميزة وصفة
        $pdo->prepare("UPDATE subscriptions SET used_prescriptions = used_prescriptions + 1 WHERE id=?")->execute([$sub['id']]);

        // إشعار للطبيب
        $stmt = $pdo->prepare("SELECT user_id FROM doctors WHERE id=?");
        $stmt->execute([$doctor_id]);
        $doctor_user_id = $stmt->fetchColumn();
        if ($doctor_user_id) {
            require_once '../functions/notifications.php';
            add_notification($pdo, $doctor_user_id, 'prescription_request', $prescription_id, "لديك طلب وصفة طبية جديدة من مريض.");
        }
        $success = "تم إرسال طلب الوصفة للطبيب المختار.";
    }
}

// معالجة زر طلب تجديد الوصفة
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['renew_prescription_id'])) {
    $presc_id = intval($_POST['renew_prescription_id']);
    // جلب بيانات الوصفة للتأكد من الملكية
    $stmt = $pdo->prepare("SELECT * FROM prescriptions WHERE id=? AND patient_id=?");
    $stmt->execute([$presc_id, $_SESSION['user_id']]);
    $presc = $stmt->fetch();
    if ($presc) {
        // تحقق عدم وجود طلب معلق لنفس الوصفة
        $stmt2 = $pdo->prepare("SELECT id FROM prescription_renew_requests WHERE prescription_id=? AND patient_id=? AND status='pending'");
        $stmt2->execute([$presc_id, $_SESSION['user_id']]);
        if (!$stmt2->fetch()) {
            // إضافة طلب جديد
            $pdo->prepare("INSERT INTO prescription_renew_requests (prescription_id, patient_id, doctor_id, requested_at) VALUES (?, ?, ?, NOW())")
                ->execute([$presc_id, $_SESSION['user_id'], $presc['doctor_id']]);
            // إشعار للطبيب
            $stmt3 = $pdo->prepare("SELECT user_id FROM doctors WHERE id=?");
            $stmt3->execute([$presc['doctor_id']]);
            $doctor_user_id = $stmt3->fetchColumn();
            if ($doctor_user_id) {
                require_once '../functions/notifications.php';
                add_notification($pdo, $doctor_user_id, 'prescription_renew_request', $presc_id, "طلب تجديد وصفة جديد من المريض.");
            }
            $success = "تم إرسال طلب تجديد الوصفة للطبيب.";
        }
    }
}

// تنفيذ التجديد التلقائي عند زيارة الصفحة (كل 3 أشهر)
$presc_auto_renew = $pdo->prepare("SELECT * FROM prescriptions WHERE patient_id=? AND auto_renew=1");
$presc_auto_renew->execute([$_SESSION['user_id']]);
foreach($presc_auto_renew as $presc) {
    $last_renew = $pdo->prepare("SELECT requested_at FROM prescription_renew_requests WHERE prescription_id=? AND status IN ('pending','approved') ORDER BY requested_at DESC LIMIT 1");
    $last_renew->execute([$presc['id']]);
    $last = $last_renew->fetchColumn();
    $should_renew = false;
    if (!$last) $should_renew = true;
    else {
        $days = (strtotime(date('Y-m-d')) - strtotime($last)) / (60*60*24);
        if ($days >= 90) $should_renew = true;
    }
    if ($should_renew) {
        // تحقق عدم وجود طلب معلق
        $stmt2 = $pdo->prepare("SELECT id FROM prescription_renew_requests WHERE prescription_id=? AND patient_id=? AND status='pending'");
        $stmt2->execute([$presc['id'], $_SESSION['user_id']]);
        if (!$stmt2->fetch()) {
            // أضف طلب جديد
            $pdo->prepare("INSERT INTO prescription_renew_requests (prescription_id, patient_id, doctor_id, requested_at) VALUES (?, ?, ?, NOW())")
                ->execute([$presc['id'], $_SESSION['user_id'], $presc['doctor_id']]);
            // إشعار للطبيب
            $stmt3 = $pdo->prepare("SELECT user_id FROM doctors WHERE id=?");
            $stmt3->execute([$presc['doctor_id']]);
            $doctor_user_id = $stmt3->fetchColumn();
            if ($doctor_user_id) {
                require_once '../functions/notifications.php';
                add_notification($pdo, $doctor_user_id, 'prescription_renew_request', $presc['id'], "طلب تجديد وصفة تلقائي من المريض.");
            }
        }
    }
}

// جلب الوصفات المنجزة لهذا المريض
$stmt = $pdo->prepare("SELECT p.*, u.name AS doctor_name FROM prescriptions p 
    JOIN doctors d ON p.doctor_id = d.id 
    JOIN users u ON d.user_id = u.id 
    WHERE p.patient_id = ? AND (p.notes IS NOT NULL AND p.notes != '')
    ORDER BY p.created_at DESC");
$stmt->execute([$_SESSION['user_id']]);
$prescriptions = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<?php include '../includes/header.php'; ?>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
<div class="container py-5">
    <a href="index.php" class="btn btn-outline-secondary mb-3"><i class="bi bi-arrow-right"></i> رجوع</a>
    <div class="card p-4 shadow mb-4">
        <h3 class="mb-4 text-success text-center"><i class="bi bi-file-earmark-medical"></i> طلب وصفة طبية</h3>
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
                <label class="form-label">ملاحظات إضافية (اختياري)</label>
                <textarea name="notes" class="form-control" rows="3"></textarea>
            </div>
            <div class="mb-3">
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" name="auto_renew" id="auto_renew" value="1">
                  <label class="form-check-label" for="auto_renew">
                    تفعيل التجديد التلقائي لهذه الوصفة (سيتم إرسال طلب تجديد للطبيب كل 3 أشهر)
                  </label>
                </div>
            </div>
            <div class="text-center">
                <button class="btn btn-success px-5" type="submit"><i class="bi bi-send"></i> إرسال الطلب</button>
            </div>
        </form>
    </div>
    <!-- الوصفات المنجزة من الطبيب -->
    <div class="card shadow border-warning mb-4">
        <div class="card-header bg-warning text-dark fw-bold"><i class="bi bi-file-earmark-check"></i> الوصفات المنجزة من الطبيب</div>
        <div class="card-body">
            <?php foreach($prescriptions as $p): ?>
                <?php
                // تحقق هل يوجد بالفعل طلب تجديد معلق لهذه الوصفة
                $renew_stmt = $pdo->prepare("SELECT status FROM prescription_renew_requests WHERE prescription_id=? AND patient_id=? AND status='pending'");
                $renew_stmt->execute([$p['id'], $_SESSION['user_id']]);
                $has_pending_renew = $renew_stmt->fetchColumn();
                ?>
                <div class="mb-3 border-bottom pb-2">
                    <span class="fw-bold text-warning"><?= htmlspecialchars($p['doctor_name']) ?>:</span>
                    <span class="small text-muted"><?= htmlspecialchars($p['created_at']) ?></span>
                    <div class="mt-1"><strong>الوصفة:</strong> <?= nl2br(htmlspecialchars($p['notes'])) ?></div>
                    <?php if (!empty($p['file'])): ?>
                        <a href="../uploads/<?= htmlspecialchars($p['file']) ?>" class="btn btn-sm btn-outline-success mt-2" target="_blank"><i class="bi bi-download"></i> تحميل</a>
                    <?php endif; ?>
                    <div class="mt-2">
                        <b>التجديد التلقائي:</b>
                        <?php if (!empty($p['auto_renew'])): ?>
                            <span class="badge bg-success">مفعّل</span>
                        <?php else: ?>
                            <span class="badge bg-secondary">غير مفعّل</span>
                        <?php endif; ?>
                    </div>
                    <div class="mt-2">
                        <!-- زر تفعيل/إيقاف التجديد التلقائي -->
                        <form method="post" style="display:inline;">
                            <input type="hidden" name="toggle_auto_renew_id" value="<?= $p['id'] ?>">
                            <?php if (!empty($p['auto_renew'])): ?>
                                <button type="submit" class="btn btn-danger btn-sm">
                                    <i class="bi bi-x-circle"></i> إيقاف التجديد التلقائي
                                </button>
                            <?php else: ?>
                                <button type="submit" class="btn btn-success btn-sm">
                                    <i class="bi bi-arrow-repeat"></i> تفعيل التجديد التلقائي
                                </button>
                            <?php endif; ?>
                        </form>
                    </div>
                    <div class="mt-2">
                        <?php if ($has_pending_renew): ?>
                            <span class="badge bg-info">تم إرسال طلب تجديد للطبيب (بانتظار الموافقة)</span>
                        <?php else: ?>
                            <form method="post" style="display:inline;">
                                <input type="hidden" name="renew_prescription_id" value="<?= $p['id'] ?>">
                                <button type="submit" class="btn btn-outline-warning btn-sm"><i class="bi bi-arrow-repeat"></i> طلب تجديد الوصفة</button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; if(empty($prescriptions)): ?>
                <div class="text-muted text-center">لا توجد وصفات منجزة بعد.</div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php include '../includes/footer.php'; ?>