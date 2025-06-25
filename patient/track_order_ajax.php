<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
header('Content-Type: application/json');
require_once '../db.php';

$phone = trim($_POST['phone'] ?? '');

$html = '';
if (!$phone) {
    $html = '<div class="text-danger">يرجى إدخال رقم الهاتف.</div>';
} else {
    $stmt = $pdo->prepare("SELECT * FROM drug_orders WHERE phone=? ORDER BY created_at DESC");
    $stmt->execute([$phone]);
    $orders = $stmt->fetchAll();
    if ($orders) {
        foreach ($orders as $order) {
            $status_badge = '';
            switch($order['status']) {
                case 'جديد': $status_badge = '<span class="badge bg-secondary">جديد</span>'; break;
                case 'تم الاستقبال': $status_badge = '<span class="badge bg-info text-dark">تم الاستقبال</span>'; break;
                case 'جاري التحضير': $status_badge = '<span class="badge bg-warning text-dark">جاري التحضير</span>'; break;
                case 'تم التسليم': $status_badge = '<span class="badge bg-success">تم التسليم</span>'; break;
                case 'مرفوض': $status_badge = '<span class="badge bg-danger">مرفوض</span>'; break;
                default: $status_badge = htmlspecialchars($order['status']);
            }
            // عرض الوصفة إن وجدت
            $presc = $order['prescription_file'] ? "<div><b>الوصفة:</b> <a href='../prescriptions/".htmlspecialchars($order['prescription_file'])."' target='_blank'>عرض/تحميل</a></div>" : "";

            // جلب بيانات الموزع
            $distributor_info = '';
            if ($order['distributor_id']) {
                $dist_stmt = $pdo->prepare("SELECT name, phone, city FROM distributors WHERE id=?");
                $dist_stmt->execute([$order['distributor_id']]);
                $dist = $dist_stmt->fetch();
                if ($dist) {
                    $distributor_info = "<div class='mt-2 alert alert-info p-2'>
                        <b>الموزع المكلف:</b> " . htmlspecialchars($dist['name']) . "<br>
                        <b>الهاتف:</b> <a href='tel:" . htmlspecialchars($dist['phone']) . "'>" . htmlspecialchars($dist['phone']) . "</a><br>
                        <b>المدينة:</b> " . htmlspecialchars($dist['city']) . "
                    </div>";
                }
            }

            $html .= "<div class='border rounded-4 p-3 mb-3'>
                <div><b>رقم الطلب:</b> {$order['id']}</div>
                <div><b>اسم الدواء:</b> ".htmlspecialchars($order['drug_name'])."</div>
                <div><b>صيدلية رقم:</b> {$order['pharmacy_id']}</div>
                <div><b>الحالة:</b> $status_badge</div>
                <div><b>السعر:</b> {$order['price']} دج</div>
                <div class='small text-muted'><b>تاريخ الطلب:</b> {$order['created_at']}</div>
                $presc
                $distributor_info
            </div>";
        }
    } else {
        $html = '<div class="alert alert-warning">لا يوجد طلبات مرتبطة بهذا الرقم.</div>';
    }
}

echo json_encode(['html'=>$html]);