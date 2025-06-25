<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php"); exit;
}
require_once '../db.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if (!$id) { header("Location: doctors.php"); exit; }

$error = '';
$success = '';

// جلب بيانات الطبيب الحالي
$stmt = $pdo->prepare("SELECT d.*, u.name, u.email, u.phone, s.name as specialty
   FROM doctors d
   JOIN users u ON d.user_id=u.id
   LEFT JOIN specialties s ON d.specialty_id=s.id
   WHERE d.id=?");
$stmt->execute([$id]);
$doc = $stmt->fetch();
if (!$doc) { header("Location: doctors.php"); exit; }

$specialties = $pdo->query("SELECT * FROM specialties ORDER BY name")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['editDoctor'])) {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    $wilaya = trim($_POST['wilaya']);
    $work_hours = trim($_POST['work_hours']);
    $specialty_id = $_POST['specialty_id'];
    $bio = trim($_POST['bio']);

    // تحقق من البريد (إلا إذا لم يتغير)
    if ($email != $doc['email']) {
        $check = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $check->execute([$email]);
        if ($check->fetch()) {
            $error = "البريد الإلكتروني مستخدم بالفعل!";
        }
    }

    // لو لا يوجد أخطاء
    if (!$error) {
        // تحديث المستخدم
        $stmt = $pdo->prepare("UPDATE users SET name=?, email=?, phone=? WHERE id=?");
        $stmt->execute([$name, $email, $phone, $doc['user_id']]);

        // معالجة رفع صورة جديدة
        $photo = $doc['photo'];
        if (!empty($_FILES['photo']['name'])) {
            if ($photo) @unlink("../uploads/".$photo);
            $ext = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
            $photo = time().rand(1000,9999).".".$ext;
            move_uploaded_file($_FILES['photo']['tmp_name'], "../uploads/".$photo);
        }

        // تحديث الطبيب
        $stmt2 = $pdo->prepare("UPDATE doctors SET specialty_id=?, wilaya=?, address=?, work_hours=?, bio=?, photo=? WHERE id=?");
        $stmt2->execute([
            $specialty_id,
            $wilaya,
            $address,
            $work_hours,
            $bio,
            $photo,
            $id
        ]);
        $success = "تم تعديل بيانات الطبيب بنجاح!";
        // تحديث بيانات الطبيب الحالية
        $stmt->execute([$name, $email, $phone, $doc['user_id']]);
        $stmt = $pdo->prepare("SELECT d.*, u.name, u.email, u.phone, s.name as specialty
           FROM doctors d
           JOIN users u ON d.user_id=u.id
           LEFT JOIN specialties s ON d.specialty_id=s.id
           WHERE d.id=?");
        $stmt->execute([$id]);
        $doc = $stmt->fetch();
    }
}

include 'includes/header.php';
?>
<div class="container py-4">
  <div class="row justify-content-center">
    <div class="col-md-10">
      <div class="bg-white rounded-4 p-4 shadow-sm border mb-5">
        <h3 class="text-success mb-4"><i class="bi bi-pencil"></i> تعديل بيانات الطبيب</h3>
        <?php if($error): ?><div class="alert alert-danger mb-3"><?=$error?></div><?php endif; ?>
        <?php if($success): ?><div class="alert alert-success mb-3"><?=$success?></div><?php endif; ?>
        <form method="post" enctype="multipart/form-data" class="row g-3 align-items-center">
          <input type="hidden" name="editDoctor" value="1">
          <div class="col-md-3 text-center">
            <img src="<?=isset($doc['photo']) && $doc['photo'] ? '../uploads/'.htmlspecialchars($doc['photo']) : '../assets/img/doctor.png'?>" style="width:110px;height:110px;object-fit:cover;border-radius:50%;border:3px solid #36c66f;" class="mb-3 mx-auto">
            <input type="file" name="photo" class="form-control mt-2" accept="image/*">
          </div>
          <div class="col-md-9">
            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label fw-bold text-success"><i class="bi bi-person"></i> الاسم الكامل</label>
                <input required type="text" name="name" class="form-control" value="<?=htmlspecialchars($doc['name'])?>">
              </div>
              <div class="col-md-6">
                <label class="form-label fw-bold text-success"><i class="bi bi-envelope"></i> البريد الإلكتروني</label>
                <input required type="email" name="email" class="form-control" value="<?=htmlspecialchars($doc['email'])?>">
              </div>
              <div class="col-md-6">
                <label class="form-label fw-bold text-success"><i class="bi bi-phone"></i> الهاتف</label>
                <input type="text" name="phone" class="form-control" value="<?=htmlspecialchars($doc['phone'])?>">
              </div>
              <div class="col-md-6">
                <label class="form-label fw-bold text-success"><i class="bi bi-capsule"></i> التخصص</label>
                <select name="specialty_id" class="form-select" required>
                  <option value="">اختر التخصص</option>
                  <?php foreach($specialties as $s): ?><option value="<?=$s['id']?>" <?=($doc['specialty_id']==$s['id']?'selected':'')?>><?=$s['name']?></option><?php endforeach;?>
                </select>
              </div>
              <div class="col-md-3">
                <label class="form-label fw-bold text-success"><i class="bi bi-geo-alt"></i> الولاية</label>
                <input type="text" name="wilaya" class="form-control" value="<?=htmlspecialchars($doc['wilaya'])?>">
              </div>
              <div class="col-md-3">
                <label class="form-label fw-bold text-success"><i class="bi bi-geo"></i> العنوان التفصيلي</label>
                <input type="text" name="address" class="form-control" value="<?=isset($doc['address'])?htmlspecialchars($doc['address']):''?>">
              </div>
              <div class="col-md-6">
                <label class="form-label fw-bold text-success"><i class="bi bi-clock-history"></i> أوقات العمل</label>
                <input type="text" name="work_hours" class="form-control" value="<?=isset($doc['work_hours'])?htmlspecialchars($doc['work_hours']):''?>">
              </div>
              <div class="col-12">
                <label class="form-label fw-bold text-success"><i class="bi bi-info-circle"></i> نبذة عن الطبيب</label>
                <textarea name="bio" class="form-control" rows="2"><?=htmlspecialchars($doc['bio'])?></textarea>
              </div>
              <div class="col-12 text-end mt-3">
                <button type="submit" class="btn btn-success px-5 fw-bold"><i class="bi bi-save"></i> حفظ التعديلات</button>
                <a href="doctors.php" class="btn btn-secondary px-4 ms-2">رجوع</a>
              </div>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
<?php include 'includes/footer.php'; ?>