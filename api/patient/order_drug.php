<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'patient') { 
    header("Location: ../auth/login.php"); 
    exit; 
}
require_once '../db.php';

// جلب قائمة الصيدليات
$pharmacies = $pdo->query("SELECT * FROM services WHERE type='pharmacy' ORDER BY name")->fetchAll();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pharmacy_id = intval($_POST['pharmacy_id']);
    $drug_name = trim($_POST['drug_name']);
    $delivery = $_POST['delivery'];
    $notes = trim($_POST['notes'] ?? "");
    $phone = trim($_POST['phone'] ?? "");
    $prescription_file = null;

    // رفع الوصفة الطبية (إجباري أو اختياري حسب الحاجة)
    if (!empty($_FILES['prescription_file']['name'])) {
        $ext = strtolower(pathinfo($_FILES['prescription_file']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg','jpeg','png','pdf','gif'];
        if (!in_array($ext, $allowed)) {
            $error = "صيغة الملف غير مدعومة!";
        } else {
            $fname = uniqid('presc_').'.'.$ext;
            if (!is_dir("../prescriptions")) mkdir("../prescriptions", 0777, true);
            if (move_uploaded_file($_FILES['prescription_file']['tmp_name'], "../prescriptions/$fname")) {
                $prescription_file = $fname;
            } else {
                $error = 'فشل رفع الوصفة الطبية!';
            }
        }
    }

    // جلب السعر من قاعدة بيانات الدواء
    $stmt = $pdo->prepare("SELECT price FROM pharmacy_drugs WHERE pharmacy_id=? AND name=?");
    $stmt->execute([$pharmacy_id, $drug_name]);
    $price = $stmt->fetchColumn();
    $price = $price ? intval($price) : 0;
    $delivery_fee = ($delivery == 'delivery') ? 300 : 0;
    $total_price = $price + $delivery_fee;

    if (!$pharmacy_id || !$drug_name) {
        $error = "يرجى اختيار الصيدلية واسم الدواء";
    } elseif ($price == 0) {
        $error = "يرجى اختيار دواء من قائمة الصيدلية";
    } elseif (!$phone || !preg_match('/^0[567]\d{8}$/', $phone)) {
        $error = "يرجى إدخال رقم هاتف صحيح (يبدأ بـ 05 أو 06 أو 07 و10 أرقام)";
    } elseif (!$prescription_file) {
        $error = "يرجى رفع وصفة طبية (صورة أو PDF)"; // إذا كان رفع الوصفة إجباري
    }

    if (!$error) {
        $stmt = $pdo->prepare("INSERT INTO drug_orders (patient_id, pharmacy_id, drug_name, price, notes, delivery, phone, prescription_file, status, created_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'جديد', NOW())");
        $stmt->execute([$_SESSION['user_id'], $pharmacy_id, $drug_name, $total_price, $notes, $delivery, $phone, $prescription_file]);
        $success = "تم إرسال طلب الدواء بنجاح! السعر النهائي: <b>$total_price دج</b>";

        // إشعار الصيدلية بوجود طلب جديد
        $notif_msg = "وصل طلب جديد لدواء ($drug_name) من أحد المرضى.";
        $notif_type = "order_drug";
        $stmt = $pdo->prepare("SELECT id FROM users WHERE role='pharmacy' AND pharmacy_id=? LIMIT 1");
        $stmt->execute([$pharmacy_id]);
        $pharmacy_user_id = $stmt->fetchColumn();
        if ($pharmacy_user_id) {
            $stmt = $pdo->prepare("INSERT INTO notifications (user_id, message, type, created_at) VALUES (?, ?, ?, NOW())");
            $stmt->execute([$pharmacy_user_id, $notif_msg, $notif_type]);
        }
    }
}
?>
<?php include '../includes/header.php'; ?>
<div class="container py-5">
    <a href="index.php" class="btn btn-outline-secondary mb-3"><i class="bi bi-arrow-right"></i> رجوع</a>
    <div class="card p-4 shadow mb-4">
        <h3 class="mb-4 text-success text-center"><i class="bi bi-capsule"></i> طلب دواء من صيدلية</h3>
        <?php if($error): ?><div class="alert alert-danger"><?=$error?></div><?php endif; ?>
        <?php if($success): ?><div class="alert alert-success"><?=$success?></div><?php endif; ?>
        <form method="post" enctype="multipart/form-data" autocomplete="off">
            <div class="mb-3">
                <label class="form-label">اختر الصيدلية</label>
                <select name="pharmacy_id" id="pharmacy_id" class="form-select" required>
                    <option value="">-- اختر الصيدلية --</option>
                    <?php foreach($pharmacies as $ph): ?>
                        <option value="<?=$ph['id']?>"><?=$ph['name']?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">اسم الدواء</label>
                <input list="drugs_list" name="drug_name" id="drug_name" class="form-control" required>
                <datalist id="drugs_list"></datalist>
            </div>
            <div class="mb-3">
                <label class="form-label">رقم الهاتف للتواصل</label>
                <input type="text" name="phone" class="form-control" required pattern="^0[567]\d{8}$" placeholder="06xxxxxxxx">
            </div>
            <div class="mb-3">
                <label class="form-label">طريقة الاستلام</label><br>
                <input type="radio" name="delivery" value="pickup" checked> أستلم من الصيدلية
                <input type="radio" name="delivery" value="delivery" class="ms-3"> توصيل للمنزل (+300 دج)
            </div>
            <div class="mb-3">
                <label class="form-label">ملاحظات إضافية (اختياري)</label>
                <textarea name="notes" class="form-control" rows="2"></textarea>
            </div>
            <div class="mb-3">
                <label class="form-label">ارفع وصفة طبية (PDF, صورة)</label>
                <input type="file" name="prescription_file" class="form-control" accept="image/*,.pdf" required>
            </div>
            <div class="alert alert-info text-center mb-3">
                السعر النهائي: <span id="final_price">0 دج</span>
            </div>
            <div class="text-center">
                <button class="btn btn-success px-5" type="submit"><i class="bi bi-send"></i> إرسال الطلب</button>
            </div>
        </form>
    </div>
</div>
<script>
let drugPrices = {};
document.getElementById('pharmacy_id').addEventListener('change', function(){
    let pid = this.value;
    if(!pid) return;
    fetch('../ajax/get_drugs_by_pharmacy.php?pharmacy_id='+pid)
    .then(res=>res.json())
    .then(data=>{
        drugPrices = data;
        let datalist = document.getElementById('drugs_list');
        datalist.innerHTML = '';
        Object.keys(data).forEach(function(name){
            let option = document.createElement('option');
            option.value = name;
            datalist.appendChild(option);
        });
        document.getElementById('drug_name').value = '';
        document.getElementById('final_price').innerText = '0 دج';
    });
});
document.getElementById('drug_name').addEventListener('input', function() {
    let name = this.value.trim();
    let price = drugPrices[name] ? parseInt(drugPrices[name]) : 0;
    let delivery = document.querySelector('input[name="delivery"]:checked').value;
    let deliveryFee = (delivery === 'delivery') ? 300 : 0;
    let total = price + deliveryFee;
    document.getElementById('final_price').innerText = price ? (total + ' دج') : 'يرجى اختيار دواء من القائمة';
});
document.querySelectorAll('input[name="delivery"]').forEach(el => el.addEventListener('change', function() {
    let name = document.getElementById('drug_name').value.trim();
    let price = drugPrices[name] ? parseInt(drugPrices[name]) : 0;
    let delivery = document.querySelector('input[name="delivery"]:checked').value;
    let deliveryFee = (delivery === 'delivery') ? 300 : 0;
    let total = price + deliveryFee;
    document.getElementById('final_price').innerText = price ? (total + ' دج') : 'يرجى اختيار دواء من القائمة';
}));
</script>
<?php include '../includes/footer.php'; ?>