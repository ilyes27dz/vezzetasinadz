<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') { header("Location: ../auth/login.php"); exit; }
require_once '../db.php';

$success = '';
$error = '';

// جلب الصيدليات
$pharmacies = $pdo->query("SELECT id, name FROM services WHERE type='pharmacy' ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);

// جلب الدواء للتعديل إذا كان مطلوبًا
$edit_drug = null;
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $edit_id = intval($_GET['edit']);
    $edit_drug = $pdo->prepare("SELECT * FROM rare_drugs WHERE id=?");
    $edit_drug->execute([$edit_id]);
    $edit_drug = $edit_drug->fetch(PDO::FETCH_ASSOC);
}

// إضافة دواء نادر
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_rare_drug'])) {
    $name = trim($_POST['name'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $notes = trim($_POST['notes'] ?? '');
    $pharmacy_id = !empty($_POST['pharmacy_id']) ? intval($_POST['pharmacy_id']) : null;
    $photo = '';

    // معالجة رفع الصورة
    if (!empty($_FILES['photo']['name'])) {
        $ext = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg','jpeg','png','gif','webp'];
        if (!in_array($ext, $allowed)) {
            $error = "صيغة صورة الدواء غير مدعومة!";
        } else {
            $photo = uniqid('rare_drug_').'.'.$ext;
            if (!is_dir("../uploads/rare_drugs")) mkdir("../uploads/rare_drugs", 0777, true);
            move_uploaded_file($_FILES['photo']['tmp_name'], "../uploads/rare_drugs/$photo");
        }
    }

    if (!$name) {
        $error = "يرجى إدخال اسم الدواء.";
    } elseif (!$error) {
        $stmt = $pdo->prepare("SELECT id FROM rare_drugs WHERE name=? AND (pharmacy_id IS NULL OR pharmacy_id=?)");
        $stmt->execute([$name, $pharmacy_id]);
        if ($stmt->fetch()) {
            $error = "هذا الدواء مسجل بالفعل لهذه الصيدلية أو غير متوفر في أي صيدلية.";
        } else {
            $pdo->prepare("INSERT INTO rare_drugs (name, category, notes, pharmacy_id, photo, added_at) VALUES (?, ?, ?, ?, ?, NOW())")
                ->execute([$name, $category, $notes, $pharmacy_id, $photo]);
            $success = "تمت إضافة الدواء النادر بنجاح.";
        }
    }
}

// تحديث بيانات دواء نادر
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_rare_drug'], $_POST['edit_drug_id'])) {
    $id = intval($_POST['edit_drug_id']);
    $name = trim($_POST['name'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $notes = trim($_POST['notes'] ?? '');
    $pharmacy_id = !empty($_POST['pharmacy_id']) ? intval($_POST['pharmacy_id']) : null;

    $set = "name=?, category=?, notes=?, pharmacy_id=?";
    $params = [$name, $category, $notes, $pharmacy_id];
    // معالجة صورة جديدة
    if (!empty($_FILES['photo']['name'])) {
        $ext = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg','jpeg','png','gif','webp'];
        if (in_array($ext, $allowed)) {
            $photo = uniqid('rare_drug_').'.'.$ext;
            if (!is_dir("../uploads/rare_drugs")) mkdir("../uploads/rare_drugs", 0777, true);
            move_uploaded_file($_FILES['photo']['tmp_name'], "../uploads/rare_drugs/$photo");
            // حذف القديمة
            $old = $pdo->query("SELECT photo FROM rare_drugs WHERE id=$id")->fetch();
            if ($old && $old['photo']) @unlink("../uploads/rare_drugs/".$old['photo']);
            $set .= ", photo=?";
            $params[] = $photo;
        }
    }
    $params[] = $id;
    $pdo->prepare("UPDATE rare_drugs SET $set WHERE id=?")->execute($params);
    header("Location: rare_drugs.php?updated=1");
    exit;
}

// حذف دواء (مع حذف الصورة)
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $row = $pdo->query("SELECT photo FROM rare_drugs WHERE id=$id")->fetch();
    if ($row && isset($row['photo']) && $row['photo']) @unlink("../uploads/rare_drugs/".$row['photo']);
    $pdo->prepare("DELETE FROM rare_drugs WHERE id=?")->execute([$id]);
    header("Location: rare_drugs.php?deleted=1"); exit;
}

// قائمة الأدوية النادرة
$rare_drugs = $pdo->query(
    "SELECT r.*, s.name AS pharmacy_name
     FROM rare_drugs r
     LEFT JOIN services s ON r.pharmacy_id=s.id
     ORDER BY r.added_at DESC"
)->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8">
  <title>إدارة الأدوية النادرة</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@500;700&display=swap" rel="stylesheet">
  <style>
    body {background: #f7fcff;}
    .main-content-admin { margin-right: 240px; padding: 90px 36px 16px 16px; min-height: 100vh; font-family: 'Tajawal', 'Cairo', Tahoma, Arial, sans-serif; transition: background 0.2s;}
    @media (max-width:991px){.main-content-admin { margin-right: 0; padding: 70px 6px 10px 6px;}}
    .rare-form-custom {background: linear-gradient(120deg,#f2fff7 70%,#e0f7fa 100%);border: 2px solid #14e49c44;}
    .rare-title {color: #14e49c;font-weight: bold;letter-spacing: .02em;}
    .back-btn-top {margin-bottom: 18px;font-size: 1.07rem;font-weight: bold;border-radius: 12px;padding: 7px 20px;color: #0db98d;border: 2px solid #0db98d44;background: #e4fff7;transition: background .13s, color .13s;display: inline-flex; align-items: center; gap: 6px;}
    .back-btn-top:hover {background:#0db98d; color:#fff;}
    .form-label {font-weight:bold;}
    .table th, .table td {vertical-align: middle;}
    .rare-drug-img {width: 62px; height: 62px; object-fit: cover; border-radius: 10px; border: 2px solid #17a2b8;}
    .file-upload-col { min-width: 160px; }
    .file-upload-btn {
      height: 38px;
      width: 100%;
      border: 2px dashed #0db98d88;
      background: #e8fdf6;
      color: #0db98d;
      font-weight: bold;
      display: flex;
      align-items: center;
      justify-content: center;
      border-radius: 10px;
      cursor: pointer;
      transition: border-color 0.15s, background 0.15s;
    }
    .file-upload-btn:hover {
      border-color: #0db98d;
      background: #d2fff0;
    }
    .file-upload-btn input[type=file] {
      display: none;
    }
    .file-upload-label {
      cursor: pointer;
      font-size: 1.06em;
      display: flex;
      align-items: center;
      gap: 6px;
    }
    .file-upload-selected {
      font-size: .96em;
      color: #088d67;
      margin-top: 5px;
      direction: ltr;
      text-align: left;
      overflow-x: auto;
    }
  </style>
</head>
<body>
<?php include 'includes/navbar.php'; ?>
<?php include 'includes/sidebar.php'; ?>
<div class="main-content-admin">
    <a href="dashboard.php" class="back-btn-top"><i class="bi bi-arrow-right"></i> رجوع للواجهة</a>
    <h2 class="mb-4 rare-title"><i class="bi bi-exclamation-circle"></i> إدارة الأدوية النادرة</h2>
    <?php if($error): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>
    <?php if($success): ?><div class="alert alert-success"><?= $success ?></div><?php endif; ?>
    <?php if(isset($_GET['deleted'])): ?><div class="alert alert-success">تم حذف الدواء بنجاح.</div><?php endif; ?>
    <?php if(isset($_GET['updated'])): ?><div class="alert alert-success">تم حفظ التعديلات بنجاح.</div><?php endif; ?>

    <!-- نموذج تعديل دواء نادر -->
    <?php if($edit_drug): ?>
    <form method="post" enctype="multipart/form-data" class="rare-form-custom border rounded-3 mb-4 p-4 shadow-sm">
        <input type="hidden" name="edit_drug_id" value="<?= $edit_drug['id'] ?>">
        <div class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label mb-1 text-success"><i class="bi bi-capsule"></i> اسم الدواء</label>
                <input type="text" name="name" class="form-control mb-2" value="<?=htmlspecialchars($edit_drug['name'])?>" required>
            </div>
            <div class="col-md-3">
                <label class="form-label mb-1 text-success"><i class="bi bi-tags"></i> الفئة</label>
                <input type="text" name="category" class="form-control mb-2" value="<?=htmlspecialchars($edit_drug['category'])?>">
            </div>
            <div class="col-md-3">
                <label class="form-label mb-1 text-success"><i class="bi bi-building"></i> الصيدلية المستهدفة</label>
                <select name="pharmacy_id" class="form-select mb-2">
                    <option value="">غير متوفر في أي صيدلية</option>
                    <?php foreach($pharmacies as $ph): ?>
                    <option value="<?= $ph['id'] ?>" <?=($edit_drug['pharmacy_id']==$ph['id'])?'selected':''?>><?= htmlspecialchars($ph['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label mb-1 text-success"><i class="bi bi-chat-left-text"></i> ملاحظات</label>
                <input type="text" name="notes" class="form-control mb-2" value="<?=htmlspecialchars($edit_drug['notes'])?>">
            </div>
            <div class="col-md-1 file-upload-col text-center">
                <label class="form-label mb-1 text-success"><i class="bi bi-image"></i> صورة الدواء</label>
                <label class="file-upload-btn">
                  <span class="file-upload-label"><i class="bi bi-upload"></i> اختر صورة</span>
                  <input type="file" name="photo" onchange="showSelectedFile(this)">
                </label>
                <div class="file-upload-selected" id="edit-file-selected"></div>
                <?php if($edit_drug['photo']): ?>
                    <img src="../uploads/rare_drugs/<?=htmlspecialchars($edit_drug['photo'])?>" style="width:48px; margin-top:7px; border-radius:7px; border:1.5px solid #14e49c;" alt="صورة الدواء">
                <?php endif;?>
            </div>
            <div class="col-12 text-center mt-2">
                <button type="submit" name="update_rare_drug" class="btn btn-primary px-5">
                    <i class="bi bi-save"></i> حفظ التعديلات
                </button>
                <a href="rare_drugs.php" class="btn btn-outline-secondary mx-2">إلغاء</a>
            </div>
        </div>
    </form>
    <?php else: ?>
    <!-- نموذج إضافة دواء نادر -->
    <form method="post" enctype="multipart/form-data" class="rare-form-custom border rounded-3 mb-4 p-4 shadow-sm">
        <div class="row g-3 align-items-end">
          <div class="col-md-3">
            <label class="form-label mb-1 text-success"><i class="bi bi-capsule"></i> اسم الدواء</label>
            <input type="text" name="name" class="form-control mb-2" placeholder="اسم الدواء" required>
          </div>
          <div class="col-md-3">
            <label class="form-label mb-1 text-success"><i class="bi bi-tags"></i> الفئة</label>
            <input type="text" name="category" class="form-control mb-2" placeholder="مثال: أعصاب، قلب ...">
          </div>
          <div class="col-md-3">
            <label class="form-label mb-1 text-success"><i class="bi bi-building"></i> الصيدلية المستهدفة</label>
            <select name="pharmacy_id" class="form-select mb-2">
                <option value="">غير متوفر في أي صيدلية</option>
                <?php foreach($pharmacies as $ph): ?>
                <option value="<?= $ph['id'] ?>"><?= htmlspecialchars($ph['name']) ?></option>
                <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-2">
            <label class="form-label mb-1 text-success"><i class="bi bi-chat-left-text"></i> ملاحظات</label>
            <input type="text" name="notes" class="form-control mb-2" placeholder="ملاحظات إضافية (اختياري)">
          </div>
          <div class="col-md-1 file-upload-col text-center">
            <label class="form-label mb-1 text-success"><i class="bi bi-image"></i> صورة الدواء</label>
            <label class="file-upload-btn">
              <span class="file-upload-label"><i class="bi bi-upload"></i> اختر صورة</span>
              <input type="file" name="photo" onchange="showSelectedFile(this)">
            </label>
            <div class="file-upload-selected" id="add-file-selected"></div>
          </div>
        </div>
        <div class="text-center mt-3">
          <button type="submit" name="add_rare_drug" class="btn btn-success px-5">
            <i class="bi bi-plus"></i> إضافة
          </button>
        </div>
    </form>
    <?php endif; ?>

    <div class="card p-3">
      <h4 class="mb-3 text-secondary"><i class="bi bi-list-ul"></i> قائمة الأدوية النادرة</h4>
      <div class="table-responsive">
        <table class="table table-bordered align-middle text-center">
          <thead class="table-success">
            <tr>
              <th>#</th>
              <th>الصورة</th>
              <th>اسم الدواء</th>
              <th>الفئة</th>
              <th>ملاحظات</th>
              <th>الصيدلية</th>
              <th>تاريخ الإضافة</th>
              <th>إجراء</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach($rare_drugs as $i => $drug): ?>
              <tr>
                <td><?= $i+1 ?></td>
                <td>
                  <?php if($drug['photo']): ?>
                    <img src="../uploads/rare_drugs/<?=htmlspecialchars($drug['photo'])?>" class="rare-drug-img" alt="صورة الدواء">
                  <?php else: ?>
                    <span class="text-muted">لا توجد</span>
                  <?php endif; ?>
                </td>
                <td><?= htmlspecialchars($drug['name']) ?></td>
                <td><?= htmlspecialchars($drug['category']) ?></td>
                <td><?= htmlspecialchars($drug['notes']) ?></td>
                <td><?= $drug['pharmacy_name'] ? htmlspecialchars($drug['pharmacy_name']) : '<span class="text-danger">غير متوفر بأي صيدلية</span>' ?></td>
                <td><?= htmlspecialchars($drug['added_at']) ?></td>
                <td>
                  <a href="?edit=<?= $drug['id'] ?>" class="btn btn-primary btn-sm"><i class="bi bi-pencil"></i> تعديل</a>
                  <a href="?delete=<?= $drug['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('هل أنت متأكد من حذف هذا الدواء؟');"><i class="bi bi-trash"></i> حذف</a>
                </td>
              </tr>
            <?php endforeach; if(empty($rare_drugs)): ?>
              <tr><td colspan="8" class="text-muted">لا توجد أدوية نادرة مسجلة بعد.</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
</div>
<script>
function showSelectedFile(input) {
    if(!input.files.length) return;
    let fileName = input.files[0].name;
    // حدد العنصر المناسب
    let container = input.closest('form').querySelector('.file-upload-selected');
    if(container) container.innerText = fileName;
}
</script>
</body>
</html>