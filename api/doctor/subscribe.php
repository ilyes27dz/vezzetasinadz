<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'doctor') {
    header("Location: ../auth/login.php"); exit;
}
require_once '../db.php';

// خطط الاشتراك للأطباء (مثال)
$plans = [
    "شهر" => [
        "price" => 2500,
        "appointments" => 30,
        "consultations" => 60,
        "prescriptions" => 30,
        "ads" => 2
    ],
    "6 أشهر" => [
        "price" => 10000,
        "appointments" => 200,
        "consultations" => 350,
        "prescriptions" => 150,
        "ads" => 15
    ],
    "سنة" => [
        "price" => 18000,
        "appointments" => 500,
        "consultations" => 900,
        "prescriptions" => 400,
        "ads" => 35
    ]
];
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $plan = $_POST['plan'];
    $price = $plans[$plan]['price'] ?? 0;
    $appointments = $plans[$plan]['appointments'] ?? 0;
    $consultations = $plans[$plan]['consultations'] ?? 0;
    $prescriptions = $plans[$plan]['prescriptions'] ?? 0;
    $ads = $plans[$plan]['ads'] ?? 0;
    $card_number = $_POST['card_number'];
    $card_name = trim($_POST['card_name']);
    $exp_month = intval($_POST['exp_month']);
    $exp_year = intval($_POST['exp_year']);
    $cvv = $_POST['cvv'];
    $phone = $_POST['phone'];

    if (!in_array($plan, array_keys($plans))) $error = "يرجى اختيار نوع الاشتراك";
    elseif (!preg_match('/^\d{16}$/', $card_number)) $error = "رقم البطاقة يجب أن يكون 16 رقم";
    elseif (strlen($card_name) < 3) $error = "يرجى كتابة اسم حامل البطاقة كما هو في البطاقة";
    elseif ($exp_month < 1 || $exp_month > 12) $error = "شهر الانتهاء غير صحيح";
    elseif ($exp_year < intval(date('Y')) || $exp_year > intval(date('Y'))+10) $error = "سنة الانتهاء غير صحيحة";
    elseif ($exp_year == intval(date('Y')) && $exp_month < intval(date('m'))) $error = "تاريخ الانتهاء يجب أن يكون مستقبلي";
    elseif (!preg_match('/^\d{3}$/', $cvv)) $error = "كود الأمان (CVV) يجب أن يكون 3 أرقام";
    elseif (!preg_match('/^\d{10}$/', $phone)) $error = "رقم الهاتف المرتبط بالبطاقة يجب أن يكون 10 أرقام";
    else {
        $date_start = date('Y-m-d');
        $date_end = date('Y-m-d', strtotime("+" . ($plan == "شهر" ? 1 : ($plan == "6 أشهر" ? 6 : 12)) . " month"));
        // ترتيب الأعمدة والقيم بشكل صحيح!
        $pdo->prepare("INSERT INTO subscriptions (
            user_id, plan, start_date, end_date, status, payment_method, payment_ref, 
            allowed_appointments, used_appointments, 
            allowed_consultations, used_consultations, 
            allowed_prescriptions, used_prescriptions, 
            allowed_ads, used_ads
        ) VALUES (?, ?, ?, ?, 'pending', 'بطاقة ذهبية', ?, ?, 0, ?, 0, ?, 0, ?, 0)")
        ->execute([
            $_SESSION['user_id'],      // user_id
            $plan,                     // plan
            $date_start,               // start_date
            $date_end,                 // end_date
            $card_number,              // payment_ref
            $appointments,             // allowed_appointments
            $consultations,            // allowed_consultations
            $prescriptions,            // allowed_prescriptions
            $ads                       // allowed_ads
        ]);
        $sub_id = $pdo->lastInsertId();
        // إشعار للإدمن
        $admins = $pdo->query("SELECT id FROM users WHERE role='admin'")->fetchAll(PDO::FETCH_COLUMN);
        require_once '../functions/notifications.php';
        foreach ($admins as $admin_id) {
            add_notification($pdo, $admin_id, 'doctor_subscription_request', $sub_id, "طلب اشتراك جديد من طبيب.");
        }
        $success = "تم إرسال طلب الاشتراك بنجاح! سيتم تفعيل الاشتراك بعد مراجعة الإدارة.<br>نوع الاشتراك: <b>$plan</b><br>بداية: $date_start<br>نهاية: $date_end";
    }
}

// جلب آخر اشتراك للطبيب (أي حالة)
$stmt = $pdo->prepare("SELECT * FROM subscriptions WHERE user_id=? ORDER BY id DESC LIMIT 1");
$stmt->execute([$_SESSION['user_id']]);
$sub = $stmt->fetch();
$appointments_left = $sub ? max(0, intval($sub['allowed_appointments'] ?? 0) - intval($sub['used_appointments'] ?? 0)) : 0;
$consultations_left = $sub ? max(0, intval($sub['allowed_consultations'] ?? 0) - intval($sub['used_consultations'] ?? 0)) : 0;
$prescriptions_left = $sub ? max(0, intval($sub['allowed_prescriptions'] ?? 0) - intval($sub['used_prescriptions'] ?? 0)) : 0;
$ads_left = $sub ? max(0, intval($sub['allowed_ads'] ?? 0) - intval($sub['used_ads'] ?? 0)) : 0;
?>
<?php include '../includes/header.php'; ?>
<div class="container py-4">
    <a href="index.php" class="btn btn-outline-secondary mb-3"><i class="bi bi-arrow-right"></i> رجوع</a>
    <div class="row justify-content-center">
        <div class="col-lg-7">
            <?php if($sub): ?>
            <div class="card shadow-lg mb-4 border-success">
                <div class="card-header bg-success text-white"><i class="bi bi-wallet2"></i> اشتراكك الحالي</div>
                <div class="card-body">
                    <div><b>نوع الاشتراك:</b> <?=$sub['plan']?></div>
                    <div><b>من:</b> <?=$sub['start_date']?></div>
                    <div><b>إلى:</b> <span id="sub_end"><?=$sub['end_date']?></span></div>
                    <div>
                        <b>الحجوزات المتبقية:</b> <span class="fw-bold text-success"><?=$appointments_left?></span> / <?=$sub['allowed_appointments']?>
                        &nbsp;|&nbsp;
                        <b>الاستشارات المتبقية:</b> <span class="fw-bold text-success"><?=$consultations_left?></span> / <?=$sub['allowed_consultations']?>
                        &nbsp;|&nbsp;
                        <b>الوصفات المتبقية:</b> <span class="fw-bold text-success"><?=$prescriptions_left?></span> / <?=$sub['allowed_prescriptions']?>
                        &nbsp;|&nbsp;
                        <b>الإعلانات المتبقية:</b> <span class="fw-bold text-success"><?=$ads_left?></span> / <?=$sub['allowed_ads']?>
                    </div>
                    <!-- زر تحميل الفاتورة دائماً -->
                    <a href="subscription_invoice.php?id=<?=$sub['id']?>" target="_blank" class="btn btn-info mt-3">
                        <i class="bi bi-printer"></i> تحميل الفاتورة
                    </a>
                </div>
            </div>
            <?php endif; ?>
            <div class="card p-4 shadow mb-4">
                <h3 class="mb-4 text-success text-center"><i class="bi bi-credit-card"></i> الاشتراك بالبطاقة الذهبية للأطباء</h3>
                <?php if($error): ?><div class="alert alert-danger"><?=$error?></div><?php endif; ?>
                <?php if($success): ?><div class="alert alert-success"><?=$success?></div><?php endif; ?>
                <form method="post" id="subscribe_form" autocomplete="off" novalidate>
                    <div class="row g-3 mb-3">
                        <?php foreach($plans as $title=>$arr): ?>
                        <div class="col-md-4">
                            <label class="card p-3 text-center h-100 shadow-sm plan-choice" style="cursor:pointer;">
                                <input type="radio" name="plan" value="<?=$title?>" required style="display:none;">
                                <i class="bi bi-calendar-range display-5 text-primary"></i>
                                <div class="fw-bold mt-2 plan-title"><?=$title?></div>
                                <div class="text-info"><?=$arr['price']?> دج</div>
                                <div class="text-muted small">
                                    حجوزات: <b><?=$arr['appointments']?></b> |
                                    استشارات: <b><?=$arr['consultations']?></b> |
                                    وصفات: <b><?=$arr['prescriptions']?></b> |
                                    إعلانات: <b><?=$arr['ads']?></b>
                                </div>
                            </label>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <div id="chosen_plan" class="alert alert-info text-center d-none mb-3"></div>
                    <div class="mb-3">
                        <label class="form-label">رقم البطاقة الذهبية <span class="text-danger">*</span></label>
                        <input type="text" maxlength="16" pattern="\d{16}" name="card_number" class="form-control" required placeholder="مثال: 1234567890123456">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">اسم حامل البطاقة <span class="text-danger">*</span></label>
                        <input type="text" name="card_name" class="form-control" required placeholder="كما هو مكتوب على البطاقة">
                    </div>
                    <div class="row mb-3">
                        <div class="col">
                            <label class="form-label">شهر الانتهاء <span class="text-danger">*</span></label>
                            <input type="number" min="1" max="12" name="exp_month" class="form-control" required placeholder="MM">
                        </div>
                        <div class="col">
                            <label class="form-label">سنة الانتهاء <span class="text-danger">*</span></label>
                            <input type="number" min="<?=date('Y')?>" max="<?=date('Y')+10?>" name="exp_year" class="form-control" required placeholder="YYYY">
                        </div>
                        <div class="col">
                            <label class="form-label">CVV <span class="text-danger">*</span></label>
                            <input type="text" maxlength="3" pattern="\d{3}" name="cvv" class="form-control" required placeholder="مثال: 123">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">رقم الهاتف المرتبط بالبطاقة <span class="text-danger">*</span></label>
                        <input type="text" maxlength="10" pattern="\d{10}" name="phone" class="form-control" required placeholder="مثال: 0555123456">
                    </div>
                    <div class="text-center">
                        <button class="btn btn-success px-5" type="submit"><i class="bi bi-cash-coin"></i> إتمام الدفع</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<script>
document.querySelectorAll('input[name="plan"]').forEach(function(radio) {
    radio.addEventListener('change', function() {
        let label = this.closest('label');
        let planTitle = label.querySelector('.plan-title').textContent;
        document.getElementById('chosen_plan').classList.remove('d-none');
        document.getElementById('chosen_plan').innerHTML =
            'لقد اخترت باقة: <b>' + planTitle + '</b>';
    });
});
</script>
<?php include '../includes/footer.php'; ?>