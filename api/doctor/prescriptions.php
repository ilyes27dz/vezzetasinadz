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

// معالجة طلبات التجديد
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['renew_request_id'])) {
    $renew_id = intval($_POST['renew_request_id']);
    $action = $_POST['action'];
    $stmt = $pdo->prepare("SELECT * FROM prescription_renew_requests WHERE id=? AND doctor_id=? AND status='pending'");
    $stmt->execute([$renew_id, $_SESSION['user_id']]);
    $req = $stmt->fetch();

    if ($req) {
        if ($action == 'approve') {
            // جلب بيانات المريض من الوصفة الأصلية
            $pres_stmt = $pdo->prepare("SELECT * FROM prescriptions WHERE id=?");
            $pres_stmt->execute([$req['prescription_id']]);
            $old_pres = $pres_stmt->fetch();

            // بيانات جديدة من الفورم
            $notes = $_POST['notes'];
            $doctor_notes = $_POST['doctor_notes'] ?? null;
            $file = null;

            if (!empty($_FILES['file']['name'])) {
                $target = '../uploads/' . time() . '_' . basename($_FILES['file']['name']);
                if (move_uploaded_file($_FILES['file']['tmp_name'], $target)) {
                    $file = basename($target);
                }
            }

            // إنشاء وصفة جديدة
            $stmt = $pdo->prepare("INSERT INTO prescriptions (patient_id, doctor_id, notes, doctor_notes, file, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
            $stmt->execute([
                $old_pres['patient_id'],
                $doctor_id,
                $notes,
                $doctor_notes,
                $file
            ]);
            $new_prescription_id = $pdo->lastInsertId();

            // تحديث الاشتراك للطبيب: زيادة عدد الوصفات المستخدمة
            $subscription = $pdo->prepare("SELECT id FROM subscriptions WHERE user_id=? AND status='active' ORDER BY id DESC LIMIT 1");
            $subscription->execute([$_SESSION['user_id']]);
            $sub = $subscription->fetch(PDO::FETCH_ASSOC);
            if ($sub) {
                $pdo->prepare("UPDATE subscriptions SET used_prescriptions = used_prescriptions + 1 WHERE id = ?")
                    ->execute([$sub['id']]);
            }

            // تحديث الطلب كـ approved
            $pdo->prepare("UPDATE prescription_renew_requests SET status='approved', response_at=NOW() WHERE id=?")->execute([$renew_id]);
            // إشعار للمريض مع target_id = الوصفة الجديدة
            require_once '../functions/notifications.php';
            add_notification($pdo, $req['patient_id'], 'prescription_renew_approved', $new_prescription_id, "تمت الموافقة على تجديد الوصفة الطبية.");
        }
        if ($action == 'reject') {
            $pdo->prepare("UPDATE prescription_renew_requests SET status='rejected', response_at=NOW() WHERE id=?")->execute([$renew_id]);
            require_once '../functions/notifications.php';
            add_notification($pdo, $req['patient_id'], 'prescription_renew_rejected', $req['prescription_id'], "تم رفض طلب تجديد الوصفة الطبية.");
        }
    }
    header("Location: prescriptions.php");
    exit;
}

// إضافة وصفة جديدة من الطبيب (نقصان العدد)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['patient_id'], $_POST['notes'])) {
    $patient_id = intval($_POST['patient_id']);
    $notes = trim($_POST['notes']);
    $doctor_notes = trim($_POST['doctor_notes'] ?? '');
    $file = null;

    if (!empty($_FILES['file']['name'])) {
        $target = '../uploads/' . time() . '_' . basename($_FILES['file']['name']);
        if (move_uploaded_file($_FILES['file']['tmp_name'], $target)) {
            $file = basename($target);
        }
    }

    $stmt = $pdo->prepare("INSERT INTO prescriptions (patient_id, doctor_id, notes, doctor_notes, file, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
    $stmt->execute([
        $patient_id,
        $doctor_id,
        $notes,
        $doctor_notes,
        $file
    ]);

    // إنقاص عدد الوصفات للطبيب
    $subscription = $pdo->prepare("SELECT id FROM subscriptions WHERE user_id=? AND status='active' ORDER BY id DESC LIMIT 1");
    $subscription->execute([$_SESSION['user_id']]);
    $sub = $subscription->fetch(PDO::FETCH_ASSOC);
    if ($sub) {
        $pdo->prepare("UPDATE subscriptions SET used_prescriptions = used_prescriptions + 1 WHERE id = ?")
            ->execute([$sub['id']]);
    }

    header("Location: prescriptions.php");
    exit;
}

// جلب طلبات تجديد الوصفات المعلقة
$renew_requests = $pdo->prepare(
    "SELECT r.*, u.name as patient_name, p.notes as old_notes
     FROM prescription_renew_requests r
     JOIN prescriptions p ON r.prescription_id = p.id
     JOIN users u ON r.patient_id = u.id
     WHERE r.doctor_id = ? AND r.status = 'pending'
     ORDER BY r.requested_at DESC"
);
$renew_requests->execute([$_SESSION['user_id']]);
$renew_requests = $renew_requests->fetchAll(PDO::FETCH_ASSOC);

// جلب جميع الوصفات للطبيب مع بيانات المريض وحالة التجديد التلقائي
$stmt = $pdo->prepare("SELECT p.*, u.name AS patient_name FROM prescriptions p JOIN users u ON p.patient_id = u.id WHERE p.doctor_id = ? ORDER BY p.created_at DESC");
$stmt->execute([$doctor_id]);
$prescriptions = $stmt->fetchAll(PDO::FETCH_ASSOC);

$patients = $pdo->prepare("SELECT id, name FROM users WHERE id IN (SELECT patient_id FROM consultations WHERE doctor_id = ?)");
$patients->execute([$doctor_id]);
$patients = $patients->fetchAll(PDO::FETCH_ASSOC);
?>
<?php include '../includes/header.php'; ?>
<div class="container py-5">
    <a href="index.php" class="btn btn-light mb-3"><i class="bi bi-arrow-right"></i> رجوع للواجهة</a>
    <h3 class="mb-4 text-success">الوصفات الطبية</h3>

    <!-- طلبات تجديد الوصفات -->
    <?php if (!empty($renew_requests)): ?>
        <div class="alert alert-info">
            <h5 class="mb-3">طلبات تجديد الوصفات الطبية</h5>
            <?php foreach($renew_requests as $r): ?>
                <div class="border rounded p-3 mb-3">
                    <div>
                        <b>المريض:</b> <?= htmlspecialchars($r['patient_name']) ?><br>
                        <b>الوصفة الأصلية:</b> <?= nl2br(htmlspecialchars($r['old_notes'])) ?><br>
                        <b>تاريخ الطلب:</b> <?= htmlspecialchars($r['requested_at']) ?>
                    </div>
                    <!-- زر لإظهار نموذج التجديد -->
                    <button type="button" class="btn btn-success btn-sm mt-2"
                        onclick="document.getElementById('renew-form-<?= $r['id'] ?>').style.display='block';">
                        <i class="bi bi-check-circle"></i> الموافقة وتجديد الوصفة مع إدخال بيانات جديدة
                    </button>
                    <form method="post" class="d-inline">
                        <input type="hidden" name="renew_request_id" value="<?= $r['id'] ?>">
                        <button type="submit" name="action" value="reject" class="btn btn-danger btn-sm mt-2">
                            <i class="bi bi-x-circle"></i> رفض الطلب
                        </button>
                    </form>
                    <!-- نموذج تعبئة وصفة جديدة (مخفي) -->
                    <form method="post" enctype="multipart/form-data"
                          id="renew-form-<?= $r['id'] ?>" style="display:none; margin-top:10px;">
                        <input type="hidden" name="renew_request_id" value="<?= $r['id'] ?>">
                        <input type="hidden" name="action" value="approve">
                        <div class="row mb-2">
                            <div class="col-md-5">
                                <label>الوصفة الجديدة:</label>
                                <textarea name="notes" class="form-control" required><?= htmlspecialchars($r['old_notes']) ?></textarea>
                            </div>
                            <div class="col-md-5">
                                <label>ملاحظات الطبيب:</label>
                                <textarea name="doctor_notes" class="form-control"></textarea>
                            </div>
                            <div class="col-md-2">
                                <label>ملف جديد (اختياري):</label>
                                <input type="file" name="file" class="form-control">
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary btn-sm">اعتماد وتجديد</button>
                        <button type="button" class="btn btn-secondary btn-sm"
                            onclick="document.getElementById('renew-form-<?= $r['id'] ?>').style.display='none';">إلغاء</button>
                    </form>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form method="post" enctype="multipart/form-data" class="mb-4 border p-3 bg-light">
        <h5>إضافة وصفة جديدة</h5>
        <div class="row">
            <div class="col-md-3">
                <label>المريض:</label>
                <select name="patient_id" class="form-control" required>
                    <option value="">اختر المريض</option>
                    <?php foreach ($patients as $pat): ?>
                        <option value="<?= $pat['id'] ?>"><?= htmlspecialchars($pat['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <label>الوصفة:</label>
                <textarea name="notes" class="form-control" required></textarea>
            </div>
            <div class="col-md-3">
                <label>ملاحظات الطبيب:</label>
                <textarea name="doctor_notes" class="form-control"></textarea>
            </div>
            <div class="col-md-2">
                <label>ملف (PDF/صورة):</label>
                <input type="file" name="file" class="form-control">
            </div>
        </div>
        <button type="submit" class="btn btn-success mt-2">إضافة</button>
    </form>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>المريض</th>
                <th>تجديد تلقائي</th>
                <th>تاريخ الوصفة</th>
                <th>الوصفة</th>
                <th>ملاحظات الطبيب</th>
                <th>ملف مرفق</th>
                <th>إجراءات</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach($prescriptions as $p): ?>
            <tr>
                <td><?= htmlspecialchars($p['patient_name']) ?></td>
                <td>
                    <?php if (!empty($p['auto_renew'])): ?>
                        <span class="badge bg-info text-dark"><i class="bi bi-alarm"></i> مفعّل</span>
                    <?php else: ?>
                        <span class="badge bg-secondary">غير مفعّل</span>
                    <?php endif; ?>
                </td>
                <td><?= htmlspecialchars($p['created_at']) ?></td>
                <td><?= nl2br(htmlspecialchars($p['notes'])) ?></td>
                <td><?= $p['doctor_notes'] ? nl2br(htmlspecialchars($p['doctor_notes'])) : '<span class="text-muted">-</span>' ?></td>
                <td>
                    <?php if ($p['file']): ?>
                        <a href="../uploads/<?= htmlspecialchars($p['file']) ?>" target="_blank">تحميل</a>
                    <?php else: ?>
                        <span class="text-muted">لا يوجد</span>
                    <?php endif; ?>
                </td>
                <td>
                    <a href="edit_prescription.php?id=<?= $p['id'] ?>" class="btn btn-secondary btn-sm">تعديل</a>
                    <a href="delete_prescription.php?id=<?= $p['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('هل أنت متأكد من حذف الوصفة؟');">حذف</a>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php if (empty($prescriptions)): ?>
            <tr><td colspan="7" class="text-center">لا توجد وصفات.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>
<?php include '../includes/footer.php'; ?>