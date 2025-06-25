<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php"); exit;
}
require_once '../db.php';

$error = '';
$success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['addService'])) {
    $name = trim($_POST['name']);
    $type = $_POST['type'];
    $address = trim($_POST['address']);
    $wilaya = trim($_POST['wilaya']);
    $work_hours = trim($_POST['work_hours']);
    $phone = trim($_POST['phone']);

    // رفع الصورة
    $photo = '';
    if (!empty($_FILES['photo']['name'])) {
        $ext = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
        $photo = time().rand(1000,9999).".".$ext;
        move_uploaded_file($_FILES['photo']['tmp_name'], "../uploads/".$photo);
    }

    $stmt = $pdo->prepare("INSERT INTO services (name, type, address, wilaya, work_hours, phone, photo) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([
      $name,
      $type,
      $address,
      $wilaya,
      $work_hours,
      $phone,
      $photo
    ]);
    $success = "تمت إضافة الخدمة بنجاح!";
}

// حذف الخدمة
if (isset($_GET['del'])) {
    $id = intval($_GET['del']);
    $row = $pdo->query("SELECT photo FROM services WHERE id=$id")->fetch();
    if ($row && isset($row['photo']) && $row['photo']) @unlink("../uploads/".$row['photo']);
    $pdo->prepare("DELETE FROM services WHERE id=?")->execute([$id]);
    header("Location: services.php?deleted=1"); exit;
}
if (isset($_GET['deleted'])) $success = "تم حذف الخدمة بنجاح!";

$services = $pdo->query("SELECT * FROM services ORDER BY id DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8">
  <title>إدارة الخدمات</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@500;700&display=swap" rel="stylesheet">
  <style>
    body {background: #f7fcff;}
    .main-content-admin {
      margin-right: 240px;
      padding: 90px 36px 16px 16px;
      min-height: 100vh;
      font-family: 'Tajawal', 'Cairo', Tahoma, Arial, sans-serif;
      transition: background 0.2s;
    }
    @media (max-width:991px){
      .main-content-admin { margin-right: 0; padding: 70px 6px 10px 6px;}
    }
    .back-btn-top {
      margin-bottom: 18px;
      font-size: 1.07rem;
      font-weight: bold;
      border-radius: 12px;
      padding: 7px 20px;
      color: #0db98d;
      border: 2px solid #0db98d44;
      background: #e4fff7;
      transition: background .13s, color .13s;
      display: inline-flex; align-items: center; gap: 6px;
    }
    .back-btn-top:hover {background:#0db98d; color:#fff;}
    .service-card img {width:85px; height:85px; object-fit:cover; border-radius:16px; border:2px solid #17a2b8;}
    .service-card {border-radius:14px;}
  </style>
</head>
<body>
<?php include 'includes/navbar.php'; ?>
<?php include 'includes/sidebar.php'; ?>
<div class="main-content-admin">
  <a href="dashboard.php" class="back-btn-top"><i class="bi bi-arrow-right"></i> رجوع للواجهة</a>
  <h2 class="mb-4 text-info"><i class="bi bi-list-ul"></i> إدارة الخدمات</h2>
  <div class="card p-4 mb-4 shadow border-0">
    <h4 class="mb-3 text-info">إضافة خدمة جديدة</h4>
    <?php if($error): ?><div class="alert alert-danger mb-3"><?=$error?></div><?php endif; ?>
    <?php if($success): ?><div class="alert alert-success mb-3"><?=$success?></div><?php endif; ?>
    <form method="post" enctype="multipart/form-data" class="row g-3 align-items-center">
      <input type="hidden" name="addService" value="1">
      <div class="col-md-4">
        <label class="form-label fw-bold">اسم الخدمة</label>
        <input required type="text" name="name" class="form-control" placeholder="اسم الخدمة">
      </div>
      <div class="col-md-4">
        <label class="form-label fw-bold">نوع الخدمة</label>
        <select name="type" class="form-select" required>
          <option value="">اختر النوع</option>
          <option value="pharmacy">صيدلية</option>
          <option value="lab">مخبر تحاليل</option>
          <option value="ambulance">إسعاف</option>
          <option value="hospital">مستشفى/مصحة</option>
          <option value="imaging">مخبر أشعة</option>
          <option value="psychologist">طبيب نفسي</option>
          <option value="orthophonist">أرطوفوني</option>
          <option value="physical">علاج فيزيائي</option>
          <option value="other">أخرى</option>
        </select>
      </div>
      <div class="col-md-4">
        <label class="form-label fw-bold">العنوان التفصيلي</label>
        <input type="text" name="address" class="form-control" placeholder="العنوان (مثال: باب الزوار)">
      </div>
      <div class="col-md-4">
        <label class="form-label fw-bold">الولاية</label>
        <input type="text" name="wilaya" class="form-control" placeholder="الولاية">
      </div>
      <div class="col-md-4">
        <label class="form-label fw-bold">أوقات العمل</label>
        <input type="text" name="work_hours" class="form-control" placeholder="مثال: 08:00-22:00">
      </div>
      <div class="col-md-4">
        <label class="form-label fw-bold">الهاتف</label>
        <input type="text" name="phone" class="form-control" placeholder="رقم الهاتف">
      </div>
      <div class="col-md-4">
        <label class="form-label fw-bold">صورة الخدمة</label>
        <input type="file" name="photo" class="form-control" accept="image/*">
      </div>
      <div class="col-12 text-end">
        <button type="submit" class="btn btn-info px-5 text-white"><i class="bi bi-plus-circle"></i> إضافة الخدمة</button>
      </div>
    </form>
  </div>
  <h4 class="text-info mb-3">قائمة الخدمات</h4>
  <div class="row g-4">
    <?php foreach($services as $srv): ?>
      <div class="col-md-4">
        <div class="card service-card shadow p-3 h-100 text-center position-relative border-info border-2">
          <img src="<?=isset($srv['photo']) && $srv['photo'] ? '../uploads/'.htmlspecialchars($srv['photo']) : '../assets/img/'.htmlspecialchars($srv['type']).'.png'?>" class="mx-auto mb-2">
          <h5 class="mb-1"><?=htmlspecialchars($srv['name'])?></h5>
          <div class="text-info mb-1"><?=isset($srv['address']) ? htmlspecialchars($srv['address']) : ''?></div>
          <div class="small mb-1"><i class="bi bi-map"></i> <?=htmlspecialchars($srv['wilaya'])?></div>
          <div class="small mb-1"><i class="bi bi-clock-history"></i> <?=isset($srv['work_hours']) ? htmlspecialchars($srv['work_hours']) : ''?></div>
          <div class="small mb-1"><i class="bi bi-phone"></i> <?=htmlspecialchars($srv['phone'])?></div>
          <div class="mt-3 d-flex justify-content-center gap-2">
            <a href="?del=<?=$srv['id']?>" onclick="return confirm('تأكيد حذف الخدمة؟')" class="btn btn-danger btn-sm px-3"><i class="bi bi-trash"></i> حذف</a>
          </div>
        </div>
      </div>
    <?php endforeach;?>
    <?php if(empty($services)): ?>
      <div class="col-12"><div class="alert alert-info">لا توجد خدمات بعد.</div></div>
    <?php endif; ?>
  </div>
</div>
</body>
</html>