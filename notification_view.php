<?php
require_once 'db.php';
session_start();

if (!isset($_SESSION['user_id']) || !isset($_GET['id'])) die("Unauthorized");

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'] ?? '';

// جلب الإشعار الخاص بالمستخدم الحالي فقط
$stmt = $pdo->prepare("SELECT * FROM notifications WHERE id=? AND user_id=?");
$stmt->execute([$_GET['id'], $user_id]);
$notif = $stmt->fetch();

if (!$notif) die("Notification not found");

// اجعل الإشعار مقروءا
$pdo->prepare("UPDATE notifications SET is_read=1 WHERE id=?")->execute([$_GET['id']]);

// التوجيه حسب الدور والنوع
switch ($notif['type']) {
    case 'appointment_request':
    case 'appointment_status':
        if ($role === 'doctor') {
            header("Location: doctor/appointments.php");
        } elseif ($role === 'patient') {
            header("Location: patient/appointments.php");
        } else {
            header("Location: index.php");
        }
        break;

    case 'consultation_request':
    case 'consultation_reply':
        if ($role === 'doctor') {
            header("Location: doctor/consultations.php");
        } elseif ($role === 'patient') {
            header("Location: patient/consultation.php");
        } else {
            header("Location: index.php");
        }
        break;

    case 'prescription_request':
    case 'prescription_uploaded':
    case 'prescription_updated':
        if ($role === 'doctor') {
            header("Location: doctor/prescriptions.php");
        } elseif ($role === 'patient') {
            header("Location: patient/prescription.php");
        } else {
            header("Location: index.php");
        }
        break;

    // إشعار طلب تجديد وصفة للطبيب
    case 'prescription_renew':
        if ($role === 'doctor') {
            // توجيه الطبيب لصفحة تفاصيل الوصفة مباشرة (وليس قائمة الوصفات)
            if (!empty($notif['target_id'])) {
                header("Location: doctor/prescription_view.php?id=" . intval($notif['target_id']));
            } else {
                header("Location: doctor/prescriptions.php");
            }
        } else {
            header("Location: index.php");
        }
        break;

    // إشعار تفعيل التجديد التلقائي للوصفة للطبيب
    case 'auto_renew_enabled':
        if ($role === 'doctor') {
            if (!empty($notif['target_id'])) {
                header("Location: doctor/prescription_view.php?id=" . intval($notif['target_id']));
            } else {
                header("Location: doctor/prescriptions.php");
            }
        } else {
            header("Location: index.php");
        }
        break;

    // إشعار موافقة الطبيب على التجديد (للمريض)
    case 'prescription_renew_approved':
        if ($role === 'patient') {
            if (!empty($notif['target_id'])) {
                header("Location: patient/prescription.php?id=" . intval($notif['target_id']));
            } else {
                header("Location: patient/prescription.php");
            }
        } else {
            header("Location: index.php");
        }
        break;

    // إشعار رفض طلب التجديد (للمريض)
    case 'prescription_renew_rejected':
        if ($role === 'patient') {
            header("Location: patient/prescription.php");
        } else {
            header("Location: index.php");
        }
        break;

    case 'message':
        if ($role === 'doctor') {
            header("Location: doctor/messages.php");
        } elseif ($role === 'patient') {
            header("Location: patient/messages.php");
        } else {
            header("Location: index.php");
        }
        break;

    case 'order_drug':
        if ($role === 'pharmacy') {
            header("Location: pharmacy/orders.php");
        } elseif ($role === 'patient') {
            header("Location: patient/orders.php");
        } else {
            header("Location: index.php");
        }
        break;

    default:
        // عرض نص الإشعار فقط إذا لم يكن نوع معروف
        echo "<h4>الإشعار:</h4><p>".htmlspecialchars($notif['message'])."</p>";
        break;
}
exit;
?>