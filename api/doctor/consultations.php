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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['consultation_id'], $_POST['reply'])) {
    require_once '../functions/notifications.php';
    $cid = intval($_POST['consultation_id']);
    $reply = trim($_POST['reply']);
    $pdo->prepare("UPDATE consultations SET reply = ?, reply_date = NOW() WHERE id = ?")->execute([$reply, $cid]);

    // إنقاص عدد الاستشارات للطبيب
    $subscription = $pdo->prepare("SELECT id FROM subscriptions WHERE user_id=? AND status='active' ORDER BY id DESC LIMIT 1");
    $subscription->execute([$_SESSION['user_id']]);
    $sub = $subscription->fetch(PDO::FETCH_ASSOC);
    if ($sub) {
        $pdo->prepare("UPDATE subscriptions SET used_consultations = used_consultations + 1 WHERE id = ?")
            ->execute([$sub['id']]);
    }

    // إشعار للمريض
    $stmtConsult = $pdo->prepare("SELECT patient_id FROM consultations WHERE id = ?");
    $stmtConsult->execute([$cid]);
    $patient_id = $stmtConsult->fetchColumn();

    add_notification($pdo, $patient_id, 'consultation_reply', $cid, "تمت الإجابة على استشارتك من الطبيب.");
    header("Location: consultations.php");
    exit;
}

// جلب كل الاستشارات
$stmt = $pdo->prepare("SELECT c.*, u.name AS patient_name FROM consultations c JOIN users u ON c.patient_id = u.id WHERE c.doctor_id = ? ORDER BY c.created_at DESC");
$stmt->execute([$doctor_id]);
$consultations = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<?php include '../includes/header.php'; ?>
<div class="container py-5">
    <a href="index.php" class="btn btn-light mb-3"><i class="bi bi-arrow-right"></i> رجوع للواجهة</a>
    <h3 class="mb-4 text-success">الاستشارات والردود</h3>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>المريض</th>
                <th>الاستشارة</th>
                <th>تاريخ الإرسال</th>
                <th>الرد</th>
                <th>تاريخ الرد</th>
                <th>إجراء</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach($consultations as $c): ?>
            <tr>
                <td><?= htmlspecialchars($c['patient_name']) ?></td>
                <td><?= nl2br(htmlspecialchars($c['message'])) ?></td>
                <td><?= htmlspecialchars($c['created_at']) ?></td>
                <td><?= $c['reply'] ? nl2br(htmlspecialchars($c['reply'])) : '<span class="text-muted">لم يتم الرد</span>' ?></td>
                <td><?= $c['reply_date'] ? htmlspecialchars($c['reply_date']) : '-' ?></td>
                <td>
                    <?php if (!$c['reply']): ?>
                    <form method="post">
                        <input type="hidden" name="consultation_id" value="<?= $c['id'] ?>">
                        <textarea name="reply" class="form-control mb-2" required placeholder="اكتب الرد هنا"></textarea>
                        <button type="submit" class="btn btn-success btn-sm">إرسال الرد</button>
                    </form>
                    <?php else: ?>
                        <a href="edit_reply.php?id=<?= $c['id'] ?>" class="btn btn-warning btn-sm">تعديل الرد</a>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php if (empty($consultations)): ?>
            <tr><td colspan="6" class="text-center">لا توجد استشارات.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>
<?php include '../includes/footer.php'; ?>