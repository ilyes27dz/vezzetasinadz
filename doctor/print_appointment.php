<?php
require_once '../db.php';
if (!isset($_GET['id'])) die("رقم الموعد غير محدد!");
$id = intval($_GET['id']);
$stmt = $pdo->prepare("SELECT a.*, u.name AS patient_name, u.email 
    FROM appointments a 
    JOIN users u ON a.patient_id = u.id 
    WHERE a.id = ?");
$stmt->execute([$id]);
$a = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$a) die("الموعد غير موجود!");

$status_labels = [
    'pending' => 'قيد الدراسة',
    'confirmed' => 'مؤكد',
    'completed' => 'مكتمل',
    'cancelled' => 'ملغى'
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
            max-width: 500px;
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
            <p><label>اسم المريض:</label> <?= htmlspecialchars($a['patient_name']) ?></p>
            <p><label>البريد الإلكتروني:</label> <?= htmlspecialchars($a['email']) ?></p>
            <p>
                <label>تاريخ الموعد:</label>
                <?= date('l d/m/Y', strtotime($a['appointment_date'])) ?>
                <span style="color:#888;font-size:0.95rem;">(<?= date('Y-m-d', strtotime($a['appointment_date'])) ?>)</span>
            </p>
            <p>
                <label>الساعة:</label>
                <?= date('H:i', strtotime($a['appointment_date'])) ?>
            </p>
            <p>
                <label>حالة الموعد:</label>
                <span class="status-badge"><?= $status_labels[$a['status']] ?? htmlspecialchars($a['status']) ?></span>
            </p>
            <?php if (!empty($a['service_type'])): ?>
            <p>
                <label>الخدمة:</label>
                <?= htmlspecialchars($a['service_type']) ?>
            </p>
            <?php endif; ?>
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