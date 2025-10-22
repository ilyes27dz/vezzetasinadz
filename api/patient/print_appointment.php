<?php
require_once '../db.php';
if (!isset($_GET['id'])) die("رقم الموعد غير محدد!");
$id = intval($_GET['id']);

// جلب بيانات الموعد مع المريض والطبيب والتخصص
$stmt = $pdo->prepare("
    SELECT a.*, 
           up.name AS patient_name, up.email AS patient_email, up.phone AS patient_phone,
           ud.name AS doctor_name, d.specialty_id, s.name AS specialty
    FROM appointments a
    JOIN users up ON a.patient_id = up.id
    JOIN doctors d ON a.doctor_id = d.id
    JOIN users ud ON d.user_id = ud.id
    LEFT JOIN specialties s ON d.specialty_id = s.id
    WHERE a.id = ?
");
$stmt->execute([$id]);
$a = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$a) die("الموعد غير موجود!");

$status_labels = [
    'pending'   => 'قيد الدراسة',
    'confirmed' => 'مؤكد',
    'completed' => 'مكتمل',
    'cancelled' => 'ملغى',
    'canceled'  => 'ملغى'
];
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>ورقة إثبات موعد</title>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Cairo', Arial, sans-serif;
            background: #f7f7fa;
            margin: 0;
            padding: 0;
        }
        .proof-container {
            background: #fff;
            border-radius: 22px;
            box-shadow: 0 7px 40px 0 #0000001a;
            max-width: 540px;
            margin: 50px auto;
            padding: 35px 35px 25px 35px;
            direction: rtl;
            border: 2px solid #59986c;
        }
        .proof-header {
            display: flex;
            align-items: center;
            justify-content: center;
            border-bottom: 2px solid #e0e6e8;
            margin-bottom: 25px;
            padding-bottom: 8px;
        }
        .proof-header img {
            width: 52px;
            margin-left: 18px;
        }
        .proof-title {
            font-size: 2rem;
            color: #388e3c;
            font-weight: 700;
        }
        .proof-details {
            margin-bottom: 25px;
        }
        .proof-details p {
            font-size: 1.13rem;
            margin: 7px 0;
            color: #222;
        }
        .proof-details label {
            font-weight: 600;
            color: #59986c;
            margin-left: 6px;
        }
        .status-badge {
            display: inline-block;
            padding: 6px 18px;
            border-radius: 17px;
            font-size: 1rem;
            font-weight: bold;
            background: #e8f5e9;
            color: #388e3c;
            border: 1.5px solid #388e3c;
            margin-top: 7px;
        }
        .proof-footer {
            text-align: center;
            color: #555;
            margin-top: 30px;
            font-size: 1.07rem;
        }
        .proof-section {
            margin-bottom: 7px;
        }
        @media print {
            body { background: #fff; }
            .proof-container { box-shadow: none; border: 2px solid #388e3c; }
        }
    </style>
</head>
<body>
    <div class="proof-container">
        <div class="proof-header">
            <img src="../assets/appointment.png" alt="موعد" />
            <span class="proof-title">ورقة إثبات موعد طبي</span>
        </div>
        <div class="proof-details">
            <div class="proof-section"><label>اسم المريض:</label> <?= htmlspecialchars($a['patient_name']) ?></div>
            <div class="proof-section"><label>البريد الإلكتروني:</label> <?= htmlspecialchars($a['patient_email']) ?></div>
            <div class="proof-section"><label>رقم الهاتف:</label> <?= htmlspecialchars($a['patient_phone'] ?? '-') ?></div>
            <div class="proof-section"><label>اسم الطبيب:</label> <?= htmlspecialchars($a['doctor_name'] ?? '-') ?></div>
            <div class="proof-section"><label>التخصص:</label> <?= htmlspecialchars($a['specialty'] ?? '-') ?></div>
            <div class="proof-section">
                <label>تاريخ الموعد:</label>
                <?php
                    $dt = !empty($a['appointment_date']) ? $a['appointment_date'] : $a['date'];
                    echo date('l d/m/Y', strtotime($dt));
                ?>
                <span style="color:#888;font-size:0.95rem;">(<?= date('Y-m-d', strtotime($dt)) ?>)</span>
            </div>
            <?php
                // الوقت من appointment_date أو من عمود time
                $appt_time = '';
                if (!empty($a['appointment_date'])) $appt_time = date('H:i', strtotime($a['appointment_date']));
                elseif (!empty($a['time'])) $appt_time = htmlspecialchars($a['time']);
            ?>
            <?php if($appt_time): ?>
                <div class="proof-section"><label>الساعة:</label> <?= $appt_time ?></div>
            <?php endif; ?>
            <?php if (!empty($a['service_type'])): ?>
                <div class="proof-section"><label>الخدمة:</label> <?= htmlspecialchars($a['service_type']) ?></div>
            <?php endif; ?>
            <div class="proof-section">
                <label>حالة الموعد:</label>
                <span class="status-badge"><?= $status_labels[$a['status']] ?? htmlspecialchars($a['status']) ?></span>
            </div>
            <div class="proof-section"><label>تاريخ الحجز:</label> <?= date('Y-m-d H:i', strtotime($a['created_at'])) ?></div>
            <div class="proof-section" style="font-size:13px;color:#888;"><label>رقم الحجز:</label> <?= htmlspecialchars($a['id']) ?></div>
        </div>
        <div class="proof-footer">
            <hr style="margin:14px 0;">
            نرجو منكم الحضور قبل الموعد بـ 10 دقائق على الأقل.<br>
            لمزيد من المعلومات أو للاستفسار، يرجى التواصل مع العيادة.<br>
            <b>نتمنى لكم دوام الصحة والعافية</b>
        </div>
    </div>
    <script>window.print();</script>
</body>
</html>