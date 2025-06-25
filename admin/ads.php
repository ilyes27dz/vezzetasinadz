<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../auth/login.php"); exit;
}
require_once '../db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['title'])) {
    $title = trim($_POST['title']);
    $desc = trim($_POST['description']);
    $url = trim($_POST['url']);
    $phone = trim($_POST['phone']);
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    $img = "";
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $newname = 'ad_'.time().rand(100,999).'.'.$ext;
        move_uploaded_file($_FILES['image']['tmp_name'], "../ads/$newname");
        $img = $newname;
    }
    $stmt = $pdo->prepare("INSERT INTO medical_ads (title,image,url,description,phone,is_active) VALUES (?,?,?,?,?,?)");
    $stmt->execute([$title, $img, $url, $desc, $phone, $is_active]);
    header("Location: ads.php");
    exit;
}
if (isset($_GET['toggle'])) {
    $id = intval($_GET['toggle']);
    $pdo->query("UPDATE medical_ads SET is_active = 1 - is_active WHERE id=$id");
    header("Location: ads.php");
    exit;
}
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $pdo->query("DELETE FROM medical_ads WHERE id=$id");
    header("Location: ads.php");
    exit;
}
$ads = $pdo->query("SELECT * FROM medical_ads ORDER BY created_at DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8">
  <title>إدارة الإعلانات الطبية</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@500;700&display=swap" rel="stylesheet">
  <style>
    body {background: #f7fcff;}
    .main-content-admin {
      margin-right: 240px; padding: 90px 36px 16px 16px; min-height: 100vh; transition: background 0.2s;
      font-family: 'Tajawal', 'Cairo', Tahoma, Arial, sans-serif;
    }
    @media (max-width:991px){
      .main-content-admin { margin-right: 0; padding: 70px 6px 10px 6px;}
    }
    .ads-form-custom {background: linear-gradient(120deg,#f2fff7 70%,#e0f7fa 100%);border: 2px solid #36c66f33;}
    .ads-title {color: #36c66f;font-weight: bold;letter-spacing: .02em;}
    .ad-card-custom {border: 2px solid #36c66f22;border-radius: 16px;transition: box-shadow .11s;box-shadow: 0 2px 12px #36c66f11;background: #f8fff8;}
    .ad-card-custom:hover {box-shadow: 0 8px 28px #36c66f22;background: #f4fff7;}
    .ad-active-indicator {width: 11px; height: 11px; border-radius:50%; display:inline-block; margin-left: 6px; vertical-align:middle; border: 2px solid #fff;}
    .back-btn-top {margin-bottom: 18px;font-size: 1.07rem;font-weight: bold;border-radius: 12px;padding: 7px 20px;color: #0db98d;border: 2px solid #0db98d44;background: #e4fff7;transition: background .13s, color .13s;display: inline-flex; align-items: center; gap: 6px;}
    .back-btn-top:hover {background:#0db98d; color:#fff;}
    .form-label {font-weight:bold;}
  </style>
</head>
<body>
<?php include 'includes/navbar.php'; ?>
<?php include 'includes/sidebar.php'; ?>
<div class="main-content-admin">
    <a href="dashboard.php" class="back-btn-top"><i class="bi bi-arrow-right"></i> رجوع للواجهة</a>
    <h2 class="mb-4 ads-title"><i class="bi bi-megaphone"></i> إدارة الإعلانات الطبية</h2>
    <form method="post" enctype="multipart/form-data" class="ads-form-custom border rounded-3 mb-4 p-4 shadow-sm">
        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label mb-2 text-success"><i class="bi bi-type"></i> عنوان الإعلان</label>
                <input required type="text" name="title" class="form-control mb-2" placeholder="عنوان الإعلان">
                <label class="form-label mb-2 text-success"><i class="bi bi-link"></i> رابط خارجي (اختياري)</label>
                <input type="url" name="url" class="form-control mb-2" placeholder="مثال: https://...">
                <label class="form-label mb-2 text-success"><i class="bi bi-telephone"></i> رقم الهاتف (اختياري)</label>
                <input type="text" name="phone" class="form-control mb-2" placeholder="رقم الهاتف للإعلان">
                <label class="form-label mb-2 text-success"><i class="bi bi-image"></i> صورة الإعلان</label>
                <input type="file" name="image" class="form-control mb-2" accept="image/*">
                <label class="fw-bold mt-2">
                    <input type="checkbox" name="is_active" checked> <span class="text-success">نشط (يظهر في الموقع)</span>
                </label>
            </div>
            <div class="col-md-8">
                <label class="form-label mb-2 text-success"><i class="bi bi-align-top"></i> وصف الإعلان</label>
                <textarea required name="description" class="form-control mb-2" rows="5" placeholder="اكتب وصف الإعلان بشكل واضح وجذاب"></textarea>
                <button class="btn btn-success px-5 mt-2"><i class="bi bi-plus-circle"></i> إضافة إعلان</button>
            </div>
        </div>
    </form>
    <div class="row mt-4">
        <?php foreach($ads as $ad): ?>
        <div class="col-md-4 mb-4">
            <div class="card ad-card-custom h-100 text-center">
                <?php if($ad['image']): ?>
                    <img src="../ads/<?=htmlspecialchars($ad['image'])?>" style="max-height:85px;max-width:96%;margin:auto;margin-top:10px;border-radius:10px;" class="card-img-top" alt="">
                <?php endif; ?>
                <div class="card-body">
                    <h5 class="mb-2"><?=htmlspecialchars($ad['title'])?>
                        <span class="ad-active-indicator" style="background:<?= $ad['is_active']?'#36c66f':'#eee' ?>;" title="<?= $ad['is_active']?'نشط':'معطل' ?>"></span>
                    </h5>
                    <div class="small text-muted mb-2"><?=mb_strimwidth(htmlspecialchars($ad['description']),0,90,'...')?></div>
                    <?php if($ad['phone']): ?>
                        <div class="mb-2"><i class="bi bi-telephone text-success"></i> <b><?=htmlspecialchars($ad['phone'])?></b></div>
                    <?php endif; ?>
                    <?php if($ad['url']): ?>
                        <a href="<?=htmlspecialchars($ad['url'])?>" target="_blank" class="btn btn-outline-success btn-sm mb-2"><i class="bi bi-box-arrow-up-right"></i> رابط</a>
                    <?php endif; ?>
                    <div class="mt-2 d-flex justify-content-center gap-2">
                        <a href="?toggle=<?=$ad['id']?>" class="btn btn-<?= $ad['is_active']?'success':'secondary' ?> btn-sm px-3"><?= $ad['is_active']?'تعطيل':'تفعيل' ?></a>
                        <a href="?delete=<?=$ad['id']?>" onclick="return confirm('تأكيد حذف الإعلان؟')" class="btn btn-danger btn-sm px-3"><i class="bi bi-trash"></i> حذف</a>
                    </div>
                    <div class="text-muted mt-1 small"><?=date('d-m-Y', strtotime($ad['created_at']))?></div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
        <?php if(empty($ads)): ?>
            <div class="col-12"><div class="alert alert-info text-center">لا يوجد أي إعلان بعد.</div></div>
        <?php endif; ?>
    </div>
</div>
</body>
</html>