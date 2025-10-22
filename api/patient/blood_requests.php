<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'patient') {
    header("Location: ../auth/login.php"); exit;
}
require_once '../db.php';

// جلب كل الزمر والولايات للفلترة
$wilayas = $pdo->query("SELECT DISTINCT city FROM blood_requests WHERE is_active=1 ORDER BY city ASC")->fetchAll(PDO::FETCH_COLUMN);
$blood_types = $pdo->query("SELECT DISTINCT blood_type FROM blood_requests WHERE is_active=1 ORDER BY blood_type ASC")->fetchAll(PDO::FETCH_COLUMN);

// فلترة
$filter_wilaya = isset($_GET['wilaya']) ? trim($_GET['wilaya']) : '';
$filter_blood = isset($_GET['blood_type']) ? trim($_GET['blood_type']) : '';
$query = "SELECT * FROM blood_requests WHERE is_active=1";
if($filter_wilaya) $query .= " AND city = " . $pdo->quote($filter_wilaya);
if($filter_blood)  $query .= " AND blood_type = " . $pdo->quote($filter_blood);
$query .= " ORDER BY FIELD(urgency,'حرجة','مستعجلة','عادية'), created_at DESC";
$bloods = $pdo->query($query)->fetchAll();

include '../includes/header.php';
?>
<div class="container py-5">
    <div class="mb-4">
        <a href="index.php" class="btn btn-outline-secondary me-2 mb-2"><i class="bi bi-arrow-right"></i> العودة للوحة التحكم</a>
        <a href="../blood_request_add.php" class="btn btn-outline-danger mb-2">أضف طلب تبرع بالدم</a>
    </div>
    <h2 class="mb-4 text-danger"><i class="bi bi-droplet-half"></i> كل حالات التبرع بالدم</h2>
    <!-- فلترة -->
    <form class="row g-2 mb-4">
        <div class="col-md-4">
            <select class="form-select" name="wilaya">
                <option value="">كل الولايات</option>
                <?php foreach($wilayas as $w): ?>
                    <option value="<?=htmlspecialchars($w)?>" <?=($filter_wilaya==$w?'selected':'')?>><?=htmlspecialchars($w)?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-4">
            <select class="form-select" name="blood_type">
                <option value="">كل الزمر</option>
                <?php foreach($blood_types as $bt): ?>
                    <option value="<?=htmlspecialchars($bt)?>" <?=($filter_blood==$bt?'selected':'')?>><?=htmlspecialchars($bt)?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-4">
            <button class="btn btn-danger w-100"><i class="bi bi-funnel"></i> تطبيق الفلتر</button>
        </div>
    </form>
    <div class="row g-4 justify-content-center">
        <?php foreach($bloods as $b): ?>
            <div class="col-md-4">
                <div class="p-3 rounded-4 shadow-sm h-100 border border-<?=
                    $b['urgency']=='حرجة' ? 'danger' : ($b['urgency']=='مستعجلة'?'warning':'secondary')
                ?>">
                    <div class="fw-bold mb-2 text-<?=
                        $b['urgency']=='حرجة' ? 'danger' : ($b['urgency']=='مستعجلة'?'warning':'secondary')
                    ?>">
                        <?= $b['urgency']?> (<?=htmlspecialchars($b['blood_type'])?>)
                    </div>
                    <div class="mb-2"><?=htmlspecialchars($b['city'])?></div>
                    <div class="mb-2"><?=htmlspecialchars($b['details'])?></div>
                    <div class="text-end mb-2">
                        <span class="badge bg-success">تواصل: <?=htmlspecialchars($b['contact'])?></span>
                    </div>
                    <button 
                        class="btn btn-outline-danger btn-sm w-100"
                        data-bs-toggle="modal"
                        data-bs-target="#contactBloodModal"
                        data-contact="<?=htmlspecialchars($b['contact'])?>"
                        data-bloodtype="<?=htmlspecialchars($b['blood_type'])?>"
                        data-city="<?=htmlspecialchars($b['city'])?>"
                    >
                        <i class="bi bi-envelope-at"></i> تواصل مع صاحب الطلب
                    </button>
                </div>
            </div>
        <?php endforeach; ?>
        <?php if(!$bloods): ?>
            <div class="col-12 text-center"><div class="alert alert-info">لا يوجد طلبات في الفلتر المختار.</div></div>
        <?php endif; ?>
    </div>
</div>
<!-- مودال التواصل -->
<div class="modal fade" id="contactBloodModal" tabindex="-1" aria-labelledby="contactBloodModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form class="modal-content" id="bloodContactForm">
      <div class="modal-header">
        <h5 class="modal-title" id="contactBloodModalLabel">التواصل مع صاحب طلب التبرع بالدم</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="إغلاق"></button>
      </div>
      <div class="modal-body">
        <div id="showBloodContact"></div>
        <div class="mb-3">
          <label class="form-label">اسمك</label>
          <input type="text" name="sender_name" class="form-control" required>
        </div>
        <div class="mb-3">
          <label class="form-label">رقم هاتفك</label>
          <input type="text" name="sender_phone" class="form-control" required>
        </div>
        <div class="mb-3">
          <label class="form-label">رسالتك</label>
          <textarea name="sender_msg" class="form-control" required>السلام عليكم، أود التواصل معكم بخصوص طلب التبرع بالدم.</textarea>
        </div>
        <input type="hidden" name="owner_contact" id="owner_contact">
      </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-danger">إرسال التواصل</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إغلاق</button>
      </div>
    </form>
  </div>
</div>
<script>
var contactBloodModal = document.getElementById('contactBloodModal');
contactBloodModal.addEventListener('show.bs.modal', function (event) {
    var button = event.relatedTarget;
    var contact = button.getAttribute('data-contact');
    var bloodtype = button.getAttribute('data-bloodtype');
    var city = button.getAttribute('data-city');
    document.getElementById('owner_contact').value = contact;
    document.getElementById('showBloodContact').innerHTML =
      '<div class="alert alert-info p-2"><b>زمرة الدم:</b> ' + bloodtype + ' <b>المدينة:</b> ' + city + ' <br><b>رقم التواصل للمالك:</b> <span class="text-danger">' + contact + '</span></div>';
});
document.getElementById('bloodContactForm').addEventListener('submit', function(e){
    e.preventDefault();
    let form = this;
    document.getElementById('showBloodContact').innerHTML = '<div class="alert alert-success">تم إرسال بياناتك بنجاح! يمكنك الآن التواصل مع صاحب الطلب على الرقم: <span class="fw-bold text-danger">' + form.owner_contact.value + '</span></div>';
    setTimeout(()=>{ var modal = bootstrap.Modal.getInstance(contactBloodModal); modal.hide(); }, 3000);
    form.reset();
});
</script>
<?php include '../includes/footer.php'; ?>