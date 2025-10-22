<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'patient') { header("Location: ../auth/login.php"); exit; }
require_once '../db.php';

// خطط الاشتراك وعدد الميزات لكل ميزة
$plans = [
    "شهر" => [
        "price" => 1000,
        "appointments" => 4,
        "consultations" => 5,
        "prescriptions" => 2
    ],
    "6 أشهر" => [
        "price" => 5000,
        "appointments" => 30,
        "consultations" => 40,
        "prescriptions" => 10
    ],
    "سنة" => [
        "price" => 10000,
        "appointments" => 80,
        "consultations" => 100,
        "prescriptions" => 30
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
    $card_number = $_POST['card_number'];
    $card_name = trim($_POST['card_name']);
    $exp_month = intval($_POST['exp_month']);
    $exp_year = intval($_POST['exp_year']);
    $cvv = $_POST['cvv'];
    $phone = $_POST['phone'];

    // تحقق الشروط
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
        $pdo->prepare("INSERT INTO subscriptions (user_id, plan, start_date, end_date, status, payment_method, payment_ref, allowed_appointments, used_appointments, allowed_consultations, used_consultations, allowed_prescriptions, used_prescriptions) VALUES (?, ?, ?, ?, 'pending', 'بطاقة ذهبية', ?, ?, 0, ?, 0, ?, 0)")
            ->execute([
                $_SESSION['user_id'], $plan, $date_start, $date_end, $card_number,
                $appointments, $consultations, $prescriptions
            ]);
        $sub_id = $pdo->lastInsertId();
        // إشعار للإدمن
        $admins = $pdo->query("SELECT id FROM users WHERE role='admin'")->fetchAll(PDO::FETCH_COLUMN);
        require_once '../functions/notifications.php';
        foreach ($admins as $admin_id) {
            add_notification($pdo, $admin_id, 'subscription_request', $sub_id, "لديك طلب اشتراك جديد من مريض.");
        }
        $success = "تم إرسال طلب الاشتراك بنجاح! سيتم تفعيل الاشتراك بعد مراجعة الإدارة.<br>نوع الاشتراك: <b>$plan</b><br>بداية: $date_start<br>نهاية: $date_end";
    }
}

// جلب الاشتراك الحالي للمريض
$stmt = $pdo->prepare("SELECT * FROM subscriptions WHERE user_id=? AND status='active' AND start_date<=CURDATE() AND end_date>=CURDATE() ORDER BY id DESC LIMIT 1");
$stmt->execute([$_SESSION['user_id']]);
$sub = $stmt->fetch();

// حساب الميزات المتبقية لكل ميزة
$appointments_left = $sub ? max(0, intval($sub['allowed_appointments'] ?? 0) - intval($sub['used_appointments'] ?? 0)) : 0;
$consultations_left = $sub ? max(0, intval($sub['allowed_consultations'] ?? 0) - intval($sub['used_consultations'] ?? 0)) : 0;
$prescriptions_left = $sub ? max(0, intval($sub['allowed_prescriptions'] ?? 0) - intval($sub['used_prescriptions'] ?? 0)) : 0;
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
                    <div><b>إلى:</b> <span id="sub_end"><?=$sub['end_date']?></span>
                        <span class="badge bg-dark ms-2" id="countdown"></span>
                    </div>
                    <div>
                        <b>الحجوزات المتبقية:</b>
                        <span class="fw-bold text-success"><?=$appointments_left?></span> / <?=$sub['allowed_appointments']?>
                        &nbsp;|&nbsp;
                        <b>الاستشارات المتبقية:</b>
                        <span class="fw-bold text-success"><?=$consultations_left?></span> / <?=$sub['allowed_consultations']?>
                        &nbsp;|&nbsp;
                        <b>الوصفات المتبقية:</b>
                        <span class="fw-bold text-success"><?=$prescriptions_left?></span> / <?=$sub['allowed_prescriptions']?>
                    </div>
                    <a href="subscription_invoice.php?id=<?=$sub['id']?>" target="_blank" class="btn btn-info mt-3"><i class="bi bi-printer"></i> تحميل الفاتورة</a>
                </div>
            </div>
            <?php endif; ?>
            <div class="card p-4 shadow mb-4">
                <h3 class="mb-4 text-success text-center"><i class="bi bi-credit-card"></i> الاشتراك بالبطاقة الذهبية</h3>
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
                                    <span>حجوزات: <b><?=$arr['appointments']?></b></span>
                                    |
                                    <span>استشارات: <b><?=$arr['consultations']?></b></span>
                                    |
                                    <span>وصفات: <b><?=$arr['prescriptions']?></b></span>
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
document.querySelector('#subscribe_form').onsubmit = function(e) {
    let ok = true;
    let card = this.card_number.value.trim();
    let name = this.card_name.value.trim();
    let month = parseInt(this.exp_month.value);
    let year = parseInt(this.exp_year.value);
    let cvv = this.cvv.value.trim();
    let phone = this.phone.value.trim();
    let now = new Date();
    let error = "";
    if (!/^\d{16}$/.test(card)) { ok=false; error="رقم البطاقة يجب أن يكون 16 رقم"; }
    else if (name.length<3) { ok=false; error="اسم حامل البطاقة مطلوب"; }
    else if (!(month>=1 && month<=12)) { ok=false; error="شهر الانتهاء غير صحيح"; }
    else if (!(year>=now.getFullYear() && year<=now.getFullYear()+10)) { ok=false; error="سنة الانتهاء غير صحيحة"; }
    else if (year==now.getFullYear() && month<=(now.getMonth()+1)) { ok=false; error="تاريخ الانتهاء يجب أن يكون مستقبلي"; }
    else if (!/^\d{3}$/.test(cvv)) { ok=false; error="كود CVV يجب أن يكون 3 أرقام"; }
    else if (!/^\d{10}$/.test(phone)) { ok=false; error="رقم الهاتف يجب أن يكون 10 أرقام"; }
    if (!ok) { alert(error); e.preventDefault(); }
}
// عد تنازلي للمدة المتبقية
<?php if($sub): ?>
var dateEnd = new Date(document.getElementById("sub_end").textContent+"T23:59:59");
function updateCountdown() {
    var now = new Date();
    var diff = dateEnd - now;
    if(diff<=0) {
        document.getElementById("countdown").textContent = "انتهى الاشتراك";
        document.getElementById("countdown").className = "badge bg-danger";
        return;
    }
    var days = Math.floor(diff/(1000*60*60*24));
    var hours = Math.floor((diff/(1000*60*60))%24);
    var min = Math.floor((diff/(1000*60))%60);
    var out = days+" يوم";
    if(hours>0) out+=" و "+hours+" ساعة";
    document.getElementById("countdown").textContent = out;
}
updateCountdown();
setInterval(updateCountdown, 60*1000);
<?php endif; ?>
</script>
<?php include '../includes/footer.php'; ?>