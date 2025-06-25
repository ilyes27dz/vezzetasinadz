<?php
require_once '../db.php'; // عدّل المسار حسب مكان الملف ومشروعك

// جلب كل الوصفات التي تم تفعيل التجديد التلقائي لها ومر عليها 3 شهور أو أكثر
$sql = "SELECT * FROM prescriptions WHERE auto_renew=1 
        AND (last_renewed_at IS NULL OR last_renewed_at <= DATE_SUB(NOW(), INTERVAL 3 MONTH))";
foreach($pdo->query($sql) as $pres) {
    // إنشاء طلب تجديد جديد
    $pdo->prepare("INSERT INTO prescription_renew_requests (prescription_id, patient_id, doctor_id) VALUES (?, ?, ?)")
        ->execute([$pres['id'], $pres['patient_id'], $pres['doctor_id']]);
    // تحديث تاريخ آخر تجديد
    $pdo->prepare("UPDATE prescriptions SET last_renewed_at=NOW() WHERE id=?")->execute([$pres['id']]);
    // إشعار للطبيب
    require_once '../functions/notifications.php';
    add_notification(
        $pdo,
        $pres['doctor_id'],
        'prescription_renew',
        $pres['id'],
        "تم إنشاء طلب تجديد وصفة طبية تلقائيًا للمريض رقم #{$pres['patient_id']}."
    );
}
?>