<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php"); exit;
}
require_once '../db.php';

$error = '';
$success = '';
// إضافة طبيب جديد
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['addDoctor'])) {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $address = trim($_POST['address']);
    $wilaya = trim($_POST['wilaya']);
    $work_hours = trim($_POST['work_hours']);
    $specialty_id = $_POST['specialty_id'];
    $bio = trim($_POST['bio']);

    // تحقق من البريد
    $check = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $check->execute([$email]);
    if ($check->fetch()) {
        $error = "البريد الإلكتروني مستخدم بالفعل!";
    } else {
        // رفع الصورة
        $photo = '';
        if (!empty($_FILES['photo']['name'])) {
            $ext = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
            $photo = time().rand(1000,9999).".".$ext;
            move_uploaded_file($_FILES['photo']['tmp_name'], "../uploads/".$photo);
        }
        // أضف المستخدم
        $stmt = $pdo->prepare("INSERT INTO users (name,email,password,phone,role) VALUES (?,?,?,?,?)");
        $stmt->execute([$name, $email, $password, $phone, 'doctor']);
        $user_id = $pdo->lastInsertId();
        // أضف الطبيب مع العنوان وأوقات العمل
        $stmt2 = $pdo->prepare("INSERT INTO doctors (user_id, specialty_id, wilaya, address, work_hours, bio, photo) VALUES (?,?,?,?,?,?,?)");
        $stmt2->execute([
            $user_id,
            $specialty_id,
            $wilaya,
            $address,
            $work_hours,
            $bio,
            $photo
        ]);
        $success = "تم إضافة الطبيب بنجاح!";
    }
}

// حذف الطبيب
if (isset($_GET['del'])) {
    $doc_id = intval($_GET['del']);
    $row = $pdo->query("SELECT photo FROM doctors WHERE id=$doc_id")->fetch();
    if ($row && isset($row['photo']) && $row['photo']) @unlink("../uploads/".$row['photo']);
    $stmt = $pdo->prepare("DELETE FROM users WHERE id=(SELECT user_id FROM doctors WHERE id=?)");
    $stmt->execute([$doc_id]);
    $pdo->prepare("DELETE FROM doctors WHERE id=?")->execute([$doc_id]);
    header("Location: doctors.php?deleted=1"); exit;
}

// رسالة عند الحذف
if (isset($_GET['deleted'])) {
    $success = "تم حذف الطبيب بنجاح!";
}

// جلب الأطباء
$doctors = $pdo->query("SELECT d.*, u.name, u.email, u.phone, s.name as specialty
   FROM doctors d
   JOIN users u ON d.user_id=u.id
   LEFT JOIN specialties s ON d.specialty_id=s.id
   ORDER BY d.id DESC")->fetchAll();

$specialties = $pdo->query("SELECT * FROM specialties ORDER BY name")->fetchAll();
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8">
  <title>إدارة الأطباء</title>
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
    .doctor-card img {width:85px; height:85px; object-fit:cover; border-radius:50%; border:2px solid #0db98d;}
    .doctor-card {border-radius:14px;}
  </style>
</head>
<body>
<?php include 'includes/navbar.php'; ?>
<?php include 'includes/sidebar.php'; ?>
<div class="main-content-admin">
  <a href="dashboard.php" class="back-btn-top"><i class="bi bi-arrow-right"></i> رجوع للواجهة</a>
  <h2 class="mb-4 text-success"><i class="bi bi-person-badge"></i> إدارة الأطباء</h2>
  <div class="card p-4 mb-4 shadow border-0">
    <h4 class="mb-3 text-success">إضافة طبيب جديد</h4>
    <?php if($error): ?><div class="alert alert-danger mb-3"><?=$error?></div><?php endif; ?>
    <?php if($success): ?><div class="alert alert-success mb-3"><?=$success?></div><?php endif; ?>
    <form method="post" enctype="multipart/form-data" class="row g-3 align-items-center">
      <input type="hidden" name="addDoctor" value="1">
      <div class="col-md-4">
        <label class="form-label fw-bold">الاسم الكامل</label>
        <input required type="text" name="name" class="form-control" placeholder="اسم الطبيب">
      </div>
      <div class="col-md-4">
        <label class="form-label fw-bold">البريد الإلكتروني</label>
        <input required type="email" name="email" class="form-control" placeholder="البريد الإلكتروني">
      </div>
      <div class="col-md-4">
        <label class="form-label fw-bold">الهاتف</label>
        <input type="text" name="phone" class="form-control" placeholder="الهاتف">
      </div>
      <div class="col-md-4">
        <label class="form-label fw-bold">كلمة المرور</label>
        <input required type="password" name="password" class="form-control" placeholder="كلمة المرور">
      </div>
      <div class="col-md-4">
        <label class="form-label fw-bold">التخصص</label>
        <select name="specialty_id" class="form-select" required>
          <option value="">اختر التخصص</option>
          <?php foreach($specialties as $s): ?><option value="<?=$s['id']?>"><?=$s['name']?></option><?php endforeach;?>
        </select>
      </div>
      <div class="col-md-4">
        <label class="form-label fw-bold">الولاية</label>
        <input type="text" name="wilaya" class="form-control" placeholder="الولاية">
      </div>
      <div class="col-md-4">
        <label class="form-label fw-bold">العنوان</label>
        <input type="text" name="address" class="form-control" placeholder="العنوان (مثال: باب الزوار)">
      </div>
      <div class="col-md-4">
        <label class="form-label fw-bold">أوقات العمل</label>
        <input type="text" name="work_hours" class="form-control" placeholder="مثال: 09:00-17:00">
      </div>
      <div class="col-md-4">
        <label class="form-label fw-bold">صورة الطبيب</label>
        <input type="file" name="photo" class="form-control" accept="image/*">
      </div>
      <div class="col-12">
        <label class="form-label fw-bold">نبذة عن الطبيب</label>
        <textarea name="bio" class="form-control" placeholder="نبذة مختصرة" rows="2"></textarea>
      </div>
      <div class="col-12 text-end">
        <button type="submit" class="btn btn-success px-5"><i class="bi bi-plus-circle"></i> إضافة الطبيب</button>
      </div>
    </form>
  </div>
  <h4 class="text-success mb-3">قائمة الأطباء</h4>
  <div class="row g-4">
    <?php foreach($doctors as $doc): ?>
      <div class="col-md-4">
        <div class="card doctor-card shadow p-3 h-100 text-center position-relative border-success border-2">
          <img src="<?=isset($doc['photo']) && $doc['photo'] ? '../uploads/'.htmlspecialchars($doc['photo']) : '../assets/img/doctor.png'?>" class="mx-auto mb-2">
          <h5 class="mb-1"><?=htmlspecialchars($doc['name'])?></h5>
          <div class="text-success mb-1"><?=htmlspecialchars($doc['specialty'])?></div>
          <div class="small mb-1"><i class="bi bi-geo-alt"></i> <?=htmlspecialchars($doc['wilaya'])?></div>
          <div class="small mb-1"><i class="bi bi-geo"></i> <?=isset($doc['address']) ? htmlspecialchars($doc['address']) : ''?></div>
          <div class="small mb-1"><i class="bi bi-clock-history"></i> <?=isset($doc['work_hours']) ? htmlspecialchars($doc['work_hours']) : ''?></div>
          <div class="small mb-1"><i class="bi bi-phone"></i> <?=htmlspecialchars($doc['phone'])?></div>
          <div class="small text-secondary"><?=htmlspecialchars($doc['bio'])?></div>
          <div class="mt-3 d-flex justify-content-center gap-2">
            <a href="?del=<?=$doc['id']?>" onclick="return confirm('تأكيد حذف الطبيب؟')" class="btn btn-danger btn-sm px-3"><i class="bi bi-trash"></i> حذف</a>
          </div>
        </div>
      </div>
    <?php endforeach;?>
    <?php if(empty($doctors)): ?>
      <div class="col-12"><div class="alert alert-info">لا يوجد أطباء بعد.</div></div>
    <?php endif; ?>
  </div>
</div>
</body>
</html>