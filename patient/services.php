<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'patient') {
    header("Location: ../auth/login.php"); exit;
}
require_once '../db.php';
$type = $_GET['type'] ?? '';
$types = [
  'doctor' => 'الأطباء',
  'pharmacy' => 'الصيدليات',
  'lab' => 'مخابر التحليل',
  'ambulance' => 'سيارات الإسعاف',
  'hospital' => 'مؤسسات استشفائية',
  'imaging' => 'مخابر الأشعة',
  'psychologist' => 'مختصون نفسيون',
  'orthophonist' => 'أرطوفوني',
  'physical' => 'علاج فيزيائي',
];
include '../includes/header.php';
?>
<div class="container py-5">
    <a href="index.php" class="btn btn-outline-secondary mb-3"><i class="bi bi-arrow-right"></i> رجوع</a>

    <h2 class="mb-4 text-success">تصفح جميع الخدمات</h2>
    <?php if(!$type): ?>
      <div class="row g-3">
        <?php foreach($types as $key=>$val): ?>
          <div class="col-md-3">
            <a href="?type=<?=$key?>" class="card p-4 h-100 text-center shadow-sm text-decoration-none text-dark border-success border-2">
              <img src="../assets/img/<?=$key?>.png" alt="<?=$val?>" style="width:64px;" class="mb-2">
              <h5><?=$val?></h5>
            </a>
          </div>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <a href="services.php" class="btn btn-outline-secondary mb-3"><i class="bi bi-arrow-right"></i> رجوع</a>
      <?php
      if ($type == "doctor") {
        $specialties = $pdo->query("SELECT * FROM specialties ORDER BY name")->fetchAll();
        $spec_id = isset($_GET['spec']) ? intval($_GET['spec']) : 0;
        // أضف u.phone إلى الاستعلام
        $query = "SELECT d.*, u.name, u.phone, s.name as specialty FROM doctors d JOIN users u ON d.user_id=u.id LEFT JOIN specialties s ON d.specialty_id=s.id";
        if ($spec_id) $query .= " WHERE d.specialty_id = $spec_id";
        $query .= " ORDER BY d.id DESC";
        $rows = $pdo->query($query)->fetchAll();
        ?>
        <div class="mb-3">
          <a href="?type=doctor" class="btn btn-outline-success btn-sm mb-1<?=!isset($_GET['spec'])?' active':''?>">كل التخصصات</a>
          <?php foreach($specialties as $spec): ?>
            <a href="?type=doctor&spec=<?=$spec['id']?>" class="btn btn-outline-success btn-sm mb-1<?= isset($_GET['spec'])&&$_GET['spec']==$spec['id']?' active':'' ?>">
              <?=htmlspecialchars($spec['name'])?>
            </a>
          <?php endforeach; ?>
        </div>
        <div class="row g-4">
          <?php if($rows): foreach($rows as $doc): ?>
            <div class="col-md-4">
              <div class="card shadow p-3 h-100 text-center border-success border-2">
                <img src="<?=($doc['photo']?'../uploads/'.htmlspecialchars($doc['photo']):'../assets/img/doctor.png')?>" style="width:90px;height:90px;object-fit:cover;border-radius:50%;border:2px solid #36c66f;" class="mx-auto mb-2">
                <h5 class="mb-1"><?=htmlspecialchars($doc['name'])?></h5>
                <div class="text-success mb-1"><?=htmlspecialchars($doc['specialty'])?></div>
                <div class="small mb-1"><i class="bi bi-geo"></i> <?=isset($doc['address']) ? htmlspecialchars($doc['address']) : ''?></div>
                <div class="small mb-1"><i class="bi bi-phone"></i> <?=isset($doc['phone']) ? htmlspecialchars($doc['phone']) : '-'?></div>
                <a href="book_appointment.php" class="btn btn-success btn-sm mt-2">احجز الآن</a>
              </div>
            </div>
          <?php endforeach; else: ?>
            <div class="col-12"><div class="alert alert-info">لا يوجد أطباء في هذا التخصص حالياً</div></div>
          <?php endif;?>
        </div>
      <?php
      } else {
        $stmt = $pdo->prepare("SELECT * FROM services WHERE type=? ORDER BY id DESC");
        $stmt->execute([$type]);
        $rows = $stmt->fetchAll();
        ?>
        <div class="row g-4">
          <?php if($rows): foreach($rows as $srv): ?>
            <div class="col-md-4">
              <div class="card shadow p-3 h-100 text-center border-info border-2">
                <img src="<?=($srv['photo']?'../uploads/'.htmlspecialchars($srv['photo']):'../assets/img/'.$type.'.png')?>" style="width:90px;height:90px;object-fit:cover;border-radius:24px;border:2px solid #17a2b8;" class="mx-auto mb-2">
                <h5 class="mb-1"><?=htmlspecialchars($srv['name'])?></h5>
                <div class="text-info mb-1"><?=isset($srv['address']) ? htmlspecialchars($srv['address']) : ''?></div>
                <div class="small mb-1"><i class="bi bi-map"></i> <?=htmlspecialchars($srv['wilaya'])?></div>
                <div class="small mb-1"><i class="bi bi-clock-history"></i> <?=isset($srv['work_hours']) ? htmlspecialchars($srv['work_hours']) : ''?></div>
                <div class="small mb-1"><i class="bi bi-phone"></i> <?=isset($srv['phone']) ? htmlspecialchars($srv['phone']) : '-'?></div>
                <?php if($type=="pharmacy"): ?>
                  <a href="order_drug.php" class="btn btn-info btn-sm mt-2 text-white">طلب دواء</a>
                <?php endif; ?>
              </div>
            </div>
          <?php endforeach; else: ?>
            <div class="col-12"><div class="alert alert-info">لا يوجد عناصر حاليا في هذا القسم</div></div>
          <?php endif;?>
        </div>
      <?php } ?>
    <?php endif; ?>
</div>
<?php include '../includes/footer.php'; ?>