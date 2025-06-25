<?php
// دالة لإضافة إشعار لأي مستخدم
function add_notification($pdo, $user_id, $type, $target_id, $message) {
    $stmt = $pdo->prepare("INSERT INTO notifications (user_id, type, target_id, message, is_read, created_at) VALUES (?, ?, ?, ?, 0, NOW())");
    $stmt->execute([$user_id, $type, $target_id, $message]);
}
?>