<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'doctor') { header("Location: ../auth/login.php"); exit; }
require_once '../db.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$stmt = $pdo->prepare("SELECT s.*, u.name as doctor_name, u.email, u.phone FROM subscriptions s JOIN users u ON s.user_id=u.id WHERE s.id=? AND s.user_id=? LIMIT 1");
$stmt->execute([$id, $_SESSION['user_id']]);
$row = $stmt->fetch();

if (!$row) {
    echo "<div class='alert alert-danger text-center mt-5'>عذراً، لم يتم العثور على الفاتورة.</div>";
    exit;
}

// حساب المتبقي لكل ميزة
$allowed_appointments = intval($row['allowed_appointments'] ?? 0);
$used_appointments = intval($row['used_appointments'] ?? 0);
$allowed_consultations = intval($row['allowed_consultations'] ?? 0);
$used_consultations = intval($row['used_consultations'] ?? 0);
$allowed_prescriptions = intval($row['allowed_prescriptions'] ?? 0);
$used_prescriptions = intval($row['used_prescriptions'] ?? 0);
$allowed_ads = intval($row['allowed_ads'] ?? 0);
$used_ads = intval($row['used_ads'] ?? 0);
$platform_name = "منصة فيزيتا سينا ديزاد";
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<title>فاتورة اشتراك الطبيب - <?=$platform_name?></title>
<style>
body {font-family:'Tajawal', 'Cairo', Tahoma, Arial, sans-serif; background:#f6fbff;}
.invoice-box {
    max-width: 670px; margin:40px auto; padding:30px 40px; background:#fff; border-radius:20px;
    border:1px solid #e8e8ee; box-shadow:0 4px 30px #0db98d15;
    color:#222; position:relative;
}
.header {
    text-align:center; margin-bottom:24px;
}
.logo {
    width:90px; margin-bottom:10px;
}
.platform-name {
    color:#0db98d; font-size:2rem; font-weight:bold; letter-spacing:1px;
}
.invoice-title {
    font-size:1.3rem; color:#222; font-weight:700; margin-bottom:8px;
}
hr {border:0; border-top:2px dashed #0db98d2c; margin: 18px 0;}
.info-row {margin-bottom:14px;}
.info-label {color:#0db98d; font-weight:500;}
.info-value {font-weight:600;}
.status-badge {
    display:inline-block; padding:4px 15px; border-radius:8px; font-size:1rem;
    font-weight:600; color:#fff; background:#0db98d;
}
.status-badge.pending {background:#f1c40f;}
.status-badge.active {background:#16a085;}
.status-badge.rejected {background:#d35400;}
.status-badge.paused {background:#8395a7;}
.signature-box {
    margin-top:40px; text-align:left; font-size:.97rem; color:#0db98d; font-weight:600;
}
.signature-img {height:42px;vertical-align:middle;}
.watermark {
    position:absolute; left:50%; top:50%; transform:translate(-50%,-50%);
    opacity:.04; font-size:7rem; font-weight:bold; color:#0db98d; pointer-events:none; user-select:none;
}
@media (max-width:700px) {.invoice-box{padding:15px}}
.table-features {width:100%;margin:16px 0 6px 0;background:#fafdff;}
.table-features th,.table-features td{text-align:center;padding:8px;}
.table-features th {background:#eafbf7; color:#0db98d;}
</style>
</head>
<body>
<div class="invoice-box">
    <div class="watermark"><?=$platform_name?></div>
    <div class="header">
        <img src="../assets/img/LOGO.svg" class="logo" alt="Logo" onerror="this.style.display='none'">
        <div class="platform-name"><?=$platform_name?></div>
        <div class="invoice-title">فاتورة اشتراك طبيب</div>
    </div>
    <div class="info-row"><span class="info-label">اسم الطبيب:</span> <span class="info-value"><?=htmlspecialchars($row['doctor_name'])?></span></div>
    <div class="info-row"><span class="info-label">البريد الإلكتروني:</span> <span class="info-value"><?=htmlspecialchars($row['email'])?></span></div>
    <div class="info-row"><span class="info-label">الهاتف:</span> <span class="info-value"><?=htmlspecialchars($row['phone'])?></span></div>
    <div class="info-row"><span class="info-label">نوع الاشتراك:</span> <span class="info-value"><?=htmlspecialchars($row['plan'])?></span></div>
    <div class="info-row"><span class="info-label">من:</span> <span class="info-value"><?=htmlspecialchars($row['start_date'])?></span></div>
    <div class="info-row"><span class="info-label">إلى:</span> <span class="info-value"><?=htmlspecialchars($row['end_date'])?></span></div>
    <div class="info-row"><span class="info-label">طريقة الدفع:</span> <span class="info-value"><?=htmlspecialchars($row['payment_method'] ?? 'بطاقة ذهبية')?></span></div>
    <div class="info-row"><span class="info-label">رقم المرجع:</span> <span class="info-value"><?=htmlspecialchars($row['payment_ref'] ?? '-')?></span></div>
    <div class="info-row">
        <span class="info-label">الحالة:</span>
        <span class="status-badge <?=$row['status']?>"><?=htmlspecialchars($row['status'])?></span>
    </div>
    <!-- ✅ جدول الميزات بالتفصيل -->
    <table class="table-features" border="1" cellpadding="0" cellspacing="0">
        <thead>
            <tr>
                <th>الميزة</th>
                <th>المسموح</th>
                <th>المستخدم</th>
                <th>المتبقي</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>حجوزات المرضى</td>
                <td><?=$allowed_appointments?></td>
                <td><?=$used_appointments?></td>
                <td><?=max(0, $allowed_appointments - $used_appointments)?></td>
            </tr>
            <tr>
                <td>استشارات</td>
                <td><?=$allowed_consultations?></td>
                <td><?=$used_consultations?></td>
                <td><?=max(0, $allowed_consultations - $used_consultations)?></td>
            </tr>
            <tr>
                <td>وصفات طبية</td>
                <td><?=$allowed_prescriptions?></td>
                <td><?=$used_prescriptions?></td>
                <td><?=max(0, $allowed_prescriptions - $used_prescriptions)?></td>
            </tr>
            <tr>
                <td>إعلانات طبية</td>
                <td><?=$allowed_ads?></td>
                <td><?=$used_ads?></td>
                <td><?=max(0, $allowed_ads - $used_ads)?></td>
            </tr>
        </tbody>
    </table>
    <hr>
    <div class="signature-box">
        <span>توقيع الإدارة:</span>
        <img src="../assets/signature.jpg" alt="التوقيع" class="signature-img" onerror="this.style.display='none'">
        <span style="margin-right:18px;color:#222;font-weight:400;font-size:.98rem">تاريخ الطباعة: <?=date('Y-m-d H:i')?></span>
    </div>
</div>
<script>window.print();</script>
</body>
</html>