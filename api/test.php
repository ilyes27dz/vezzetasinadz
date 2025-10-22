<?php
require_once 'db.php';

try {
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM users");
    $result = $stmt->fetch();
    echo "✅ الاتصال نجح! عدد المستخدمين: " . $result['total'];
} catch (Exception $e) {
    echo "❌ خطأ: " . $e->getMessage();
}
?>
