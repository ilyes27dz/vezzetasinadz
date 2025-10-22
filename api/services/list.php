<?php
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
if (!isset($types[$type])) die('نوع الخدمة غير معروف');

// جلب التخصصات إن كان نوع الخدمة أطباء
$specialties = [];
if ($type == "doctor") {
    $specialties = $pdo->query("SELECT * FROM specialties ORDER BY name")->fetchAll();
    $spec_id = isset($_GET['spec']) ? intval($_GET['spec']) : 0;
    $query = "SELECT d.*, u.name, u.phone, s.name as specialty FROM doctors d JOIN users u ON d.user_id=u.id LEFT JOIN specialties s ON d.specialty_id=s.id";
    if ($spec_id) {
        $query .= " WHERE d.specialty_id = $spec_id";
    }
    $query .= " ORDER BY d.id DESC";
    $rows = $pdo->query($query)->fetchAll();
} else {
    $stmt = $pdo->prepare("SELECT * FROM services WHERE type=? ORDER BY id DESC");
    $stmt->execute([$type]);
    $rows = $stmt->fetchAll();
}
include '../includes/header.php';
?>
<div class="container py-5">
  <!-- زر العودة -->
  <div class="mb-4">
    <a href="../index.php" class="btn btn-outline-secondary me-2 mb-2"><i class="bi bi-arrow-right"></i> العودة للرئيسية</a>
    <a href="../contact.php" class="btn btn-outline-info me-2 mb-2">تواصل معنا</a>
    <a href="list.php?type=doctor" class="btn btn-outline-success mb-2">كل الخدمات</a>
  </div>
  <h2 class="mb-4 text-success"><?=$types[$type]?></h2>
  <?php if($type == "doctor"): ?>
    <div class="mb-4">
      <a href="list.php?type=doctor" class="btn btn-outline-success btn-sm mb-2<?=!isset($_GET['spec'])?' active':''?>">كل التخصصات</a>
      <?php foreach($specialties as $spec): ?>
        <a href="list.php?type=doctor&spec=<?=$spec['id']?>" class="btn btn-outline-success btn-sm mb-2<?= (isset($_GET['spec']) && $_GET['spec']==$spec['id'])?' active':'' ?>">
          <?=htmlspecialchars($spec['name'])?>
        </a>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
  <div class="row g-4">
    <?php if($type == "doctor"): ?>
      <?php if($rows): foreach($rows as $doc): ?>
        <div class="col-md-4">
          <div class="card shadow p-3 h-100 text-center border-success border-2">
            <img src="<?=($doc['photo']?'../uploads/'.htmlspecialchars($doc['photo']):'../assets/img/doctor.png')?>" style="width:110px;height:110px;object-fit:cover;border-radius:50%;border:3px solid #36c66f;" class="mx-auto mb-2">
            <h5 class="mb-1"><?=htmlspecialchars($doc['name'])?></h5>
            <div class="text-success mb-1"><?=htmlspecialchars($doc['specialty'])?></div>
            <div class="small mb-1"><i class="bi bi-geo-alt"></i> <?=htmlspecialchars($doc['wilaya'])?></div>
            <div class="small mb-1"><i class="bi bi-geo"></i> <?=isset($doc['address']) ? htmlspecialchars($doc['address']) : ''?></div>
            <div class="small mb-1"><i class="bi bi-clock-history"></i> <?=isset($doc['work_hours']) ? htmlspecialchars($doc['work_hours']) : ''?></div>
            <div class="small mb-1"><i class="bi bi-phone"></i> <?=htmlspecialchars($doc['phone'])?></div>
            <a href="../patient/book_appointment.php?doctor_id=<?=$doc['id']?>" class="btn btn-success btn-sm mt-2">سجل دخولك و قم بحجز موعدك</a>
          </div>
        </div>
      <?php endforeach; else: ?>
        <div class="col-12"><div class="alert alert-info">لا يوجد أطباء في هذا التخصص حالياً</div></div>
      <?php endif;?>
    <?php else: ?>
      <?php if($rows): foreach($rows as $srv): ?>
        <div class="col-md-4">
          <div class="card shadow p-3 h-100 text-center border-info border-2">
            <img src="<?=($srv['photo']?'../uploads/'.htmlspecialchars($srv['photo']):'../assets/img/'.$type.'.png')?>" style="width:110px;height:110px;object-fit:cover;border-radius:24px;border:3px solid #17a2b8;" class="mx-auto mb-2">
            <h5 class="mb-1"><?=htmlspecialchars($srv['name'])?></h5>
            <div class="text-info mb-1"><?=isset($srv['address']) ? htmlspecialchars($srv['address']) : ''?></div>
            <div class="small mb-1"><i class="bi bi-map"></i> <?=htmlspecialchars($srv['wilaya'])?></div>
            <div class="small mb-1"><i class="bi bi-clock-history"></i> <?=isset($srv['work_hours']) ? htmlspecialchars($srv['work_hours']) : ''?></div>
            <div class="small mb-1"><i class="bi bi-phone"></i> <?=htmlspecialchars($srv['phone'])?></div>
            <?php if($type=="pharmacy"): ?>
              <a href="../patient/order_drug.php?pharmacy_id=<?=$srv['id']?>" class="btn btn-info btn-sm mt-2 text-white">طلب دواء</a>
            <?php endif; ?>
          </div>
        </div>
      <?php endforeach; else: ?>
        <div class="col-12"><div class="alert alert-info">لا يوجد عناصر حاليا في هذا القسم</div></div>
      <?php endif;?>
    <?php endif;?>
  </div>
</div>
<?php include '../includes/footer.php'; ?>