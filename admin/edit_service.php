<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php"); exit;
}
require_once '../db.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if (!$id) { header("Location: services.php"); exit; }

$error = '';
$success = '';

// جلب بيانات الخدمة الحالية
$stmt = $pdo->prepare("SELECT * FROM services WHERE id=?");
$stmt->execute([$id]);
$srv = $stmt->fetch();
if (!$srv) { header("Location: services.php"); exit; }

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['editService'])) {
    $name = trim($_POST['name']);
    $type = $_POST['type'];
    $address = trim($_POST['address']);
    $wilaya = trim($_POST['wilaya']);
    $work_hours = trim($_POST['work_hours']);
    $phone = trim($_POST['phone']);

    // معالجة رفع صورة جديدة
    $photo = $srv['photo'];
    if (!empty($_FILES['photo']['name'])) {
        if ($photo) @unlink("../uploads/".$photo);
        $ext = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
        $photo = time().rand(1000,9999).".".$ext;
        move_uploaded_file($_FILES['photo']['tmp_name'], "../uploads/".$photo);
    }

    $stmt = $pdo->prepare("UPDATE services SET name=?, type=?, address=?, wilaya=?, work_hours=?, phone=?, photo=? WHERE id=?");
    $stmt->execute([
      $name,
      $type,
      $address,
      $wilaya,
      $work_hours,
      $phone,
      $photo,
      $id
    ]);
    $success = "تم تعديل بيانات الخدمة بنجاح!";
    // تحديث بيانات الخدمة الحالية
    $stmt->execute([
      $name,
      $type,
      $address,
      $wilaya,
      $work_hours,
      $phone,
      $photo,
      $id
    ]);
    $stmt = $pdo->prepare("SELECT * FROM services WHERE id=?");
    $stmt->execute([$id]);
    $srv = $stmt->fetch();
}

include 'includes/header.php';
?>
<div class="container py-4">
  <div class="row justify-content-center">
    <div class="col-md-10">
      <div class="bg-white rounded-4 p-4 shadow-sm border mb-5">
        <h3 class="text-info mb-4"><i class="bi bi-pencil"></i> تعديل بيانات الخدمة</h3>
        <?php if($error): ?><div class="alert alert-danger mb-3"><?=$error?></div><?php endif; ?>
        <?php if($success): ?><div class="alert alert-success mb-3"><?=$success?></div><?php endif; ?>
        <form method="post" enctype="multipart/form-data" class="row g-3 align-items-center">
          <input type="hidden" name="editService" value="1">
          <div class="col-md-3 text-center">
            <img src="<?=isset($srv['photo']) && $srv['photo'] ? '../uploads/'.htmlspecialchars($srv['photo']) : '../assets/img/'.htmlspecialchars($srv['type']).'.png'?>" style="width:110px;height:110px;object-fit:cover;border-radius:24px;border:3px solid #17a2b8;" class="mb-3 mx-auto">
            <input type="file" name="photo" class="form-control mt-2" accept="image/*">
          </div>
          <div class="col-md-9">
            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label fw-bold text-info"><i class="bi bi-list-ul"></i> اسم الخدمة</label>
                <input required type="text" name="name" class="form-control" value="<?=htmlspecialchars($srv['name'])?>">
              </div>
              <div class="col-md-6">
                <label class="form-label fw-bold text-info"><i class="bi bi-tags"></i> نوع الخدمة</label>
                <select name="type" class="form-select" required>
                  <option value="">اختر النوع</option>
                  <option value="pharmacy" <?=($srv['type']=='pharmacy'?'selected':'')?>>صيدلية</option>
                  <option value="lab" <?=($srv['type']=='lab'?'selected':'')?>>مخبر تحاليل</option>
                  <option value="ambulance" <?=($srv['type']=='ambulance'?'selected':'')?>>إسعاف</option>
                  <option value="hospital" <?=($srv['type']=='hospital'?'selected':'')?>>مستشفى/مصحة</option>
                  <option value="imaging" <?=($srv['type']=='imaging'?'selected':'')?>>مخبر أشعة</option>
                  <option value="psychologist" <?=($srv['type']=='psychologist'?'selected':'')?>>طبيب نفسي</option>
                  <option value="orthophonist" <?=($srv['type']=='orthophonist'?'selected':'')?>>أرطوفوني</option>
                  <option value="physical" <?=($srv['type']=='physical'?'selected':'')?>>علاج فيزيائي</option>
                  <option value="other" <?=($srv['type']=='other'?'selected':'')?>>أخرى</option>
                </select>
              </div>
              <div class="col-md-6">
                <label class="form-label fw-bold text-info"><i class="bi bi-geo"></i> العنوان التفصيلي</label>
                <input type="text" name="address" class="form-control" value="<?=isset($srv['address'])?htmlspecialchars($srv['address']):''?>">
              </div>
              <div class="col-md-3">
                <label class="form-label fw-bold text-info"><i class="bi bi-map"></i> الولاية</label>
                <input type="text" name="wilaya" class="form-control" value="<?=htmlspecialchars($srv['wilaya'])?>">
              </div>
              <div class="col-md-3">
                <label class="form-label fw-bold text-info"><i class="bi bi-clock-history"></i> أوقات العمل</label>
                <input type="text" name="work_hours" class="form-control" value="<?=isset($srv['work_hours'])?htmlspecialchars($srv['work_hours']):''?>">
              </div>
              <div class="col-md-6">
                <label class="form-label fw-bold text-info"><i class="bi bi-phone"></i> رقم الهاتف</label>
                <input type="text" name="phone" class="form-control" value="<?=htmlspecialchars($srv['phone'])?>">
              </div>
              <div class="col-12 text-end mt-3">
                <button type="submit" class="btn btn-info px-5 fw-bold text-white"><i class="bi bi-save"></i> حفظ التعديلات</button>
                <a href="services.php" class="btn btn-secondary px-4 ms-2">رجوع</a>
              </div>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
<?php include 'includes/footer.php'; ?>