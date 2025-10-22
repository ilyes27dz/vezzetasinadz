<?php
session_start();
if (isset($_SESSION['user_id']) && isset($_SESSION['role'])) {
    switch ($_SESSION['role']) {
        case 'admin':    header("Location: admin/dashboard.php"); exit;
        case 'doctor':   header("Location: doctor/dashboard.php"); exit;
        case 'patient':  header("Location: patient/index.php"); exit;
        case 'pharmacy': header("Location: pharmacy/dashboard.php"); exit;
    }
}
require_once 'db.php';
// قسم عروض المتبرعين بالدم (الجديد)
$donors = $pdo->query("SELECT * FROM blood_donors WHERE is_active=1 ORDER BY created_at DESC LIMIT 6")->fetchAll();
?>
<?php include 'includes/header.php'; ?>

<!-- HERO SECTION -->
<section class="hero-section text-center d-flex align-items-center justify-content-center" style="min-height: 70vh; background: linear-gradient(135deg,#e3fff7 0,#f8f8ff 100%);">
    <div class="container">
        <div class="row align-items-center flex-md-row-reverse">
            <div class="col-lg-6 mb-4 mb-lg-0">
                <img src="assets/img/doctors.png" class="img-fluid" style="max-height: 350px" alt="طب">
            </div>
            <div class="col-lg-6">
                <h1 class="display-4 fw-bold mb-3 text-success" style="font-family:'Cairo',sans-serif">مرحبا بك في منصتك الطبية الجزائرية</h1>
                <p class="lead mb-4 text-dark">كل خدمات الصحة في الجزائر بين يديك: أطباء، مخابر، صيدليات، إسعاف، استشارات عن بعد، حجوزات فورية والمزيد!</p>
                <a href="auth/register.php" class="btn btn-main btn-lg px-5 shadow mb-2">ابدأ الآن مجاناً</a>
                <a href="contact.php" class="btn btn-outline-success btn-lg px-5 ms-2 mb-2">تواصل معنا</a>
                <div class="d-block mt-3 text-muted fw-bold">
                    <i class="bi bi-check-circle text-success"></i> حجوزات فورية  
                    <i class="bi bi-check-circle text-success ms-3"></i> استشارات عن بعد  
                    <i class="bi bi-check-circle text-success ms-3"></i> خدمات متنوعة
                </div>
            </div>
        </div>
    </div>
</section>

<!-- سلايدر إعلانات متحرك وجذاب -->
<?php
$mainAds = $pdo->query("SELECT * FROM medical_ads WHERE is_active=1 ORDER BY created_at DESC LIMIT 8")->fetchAll();
if (count($mainAds) > 0):
?>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css"/>
<style>
.swiper { width: 100%; margin-bottom: 38px; margin-top: 12px; }
.swiper-slide { display: flex; align-items: center; justify-content: center; }
.ad-swiper-card {
  min-width: 290px; max-width: 390px; background: #f8fff7;
  border-radius: 18px; box-shadow: 0 2px 18px #53c2a222;
  padding: 18px 16px 13px 16px;
  text-align: center; border: 2px solid #36c66f22;
  display: flex; flex-direction: column; align-items: center; gap: 6px;
}
.ad-swiper-card img { height: 48px; max-width: 90px; margin-bottom: 5px; }
.ad-swiper-card .ad-title { font-weight: bold; color: #36c66f; font-size: 1.1rem; }
.ad-swiper-card .ad-desc { color: #666; font-size: .96rem; }
.ad-swiper-card .ad-phone { color: #17a2b8; font-weight: bold; font-size: .99rem; }
</style>
<div class="container">
    <div class="swiper">
      <div class="swiper-wrapper">
      <?php foreach($mainAds as $ad): ?>
        <div class="swiper-slide">
            <a href="<?=htmlspecialchars($ad['url'])?>" target="_blank" style="text-decoration:none">
                <div class="ad-swiper-card">
                    <?php if($ad['image']): ?>
                        <img src="ads/<?=htmlspecialchars($ad['image'])?>" alt="">
                    <?php endif; ?>
                    <div class="ad-title"><?=htmlspecialchars($ad['title'])?></div>
                    <div class="ad-desc"><?=htmlspecialchars($ad['description'])?></div>
                    <?php if(!empty($ad['phone'])): ?>
                        <div class="ad-phone"><i class="bi bi-telephone"></i> <?=htmlspecialchars($ad['phone'])?></div>
                    <?php endif; ?>
                </div>
            </a>
        </div>
      <?php endforeach; ?>
      </div>
      <div class="swiper-pagination"></div>
      <div class="swiper-button-next"></div>
      <div class="swiper-button-prev"></div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
<script>
var swiper = new Swiper('.swiper', {
  slidesPerView: 2,
  spaceBetween: 30,
  loop: true,
  autoplay: { delay: 3500, disableOnInteraction: false },
  pagination: { el: '.swiper-pagination', clickable: true },
  navigation: { nextEl: '.swiper-button-next', prevEl: '.swiper-button-prev' },
  breakpoints: { 700: { slidesPerView: 3 } }
});
</script>
<?php endif; ?>

<!-- سلايدر للخدمات -->
<section class="py-5 bg-white">
    <div class="container">
        <h2 class="text-center text-success fw-bold mb-4">خدماتنا</h2>
        <div class="row g-4 justify-content-center">
            <div class="col-6 col-md-3">
                <a href="services/list.php?type=doctor" class="service-card p-4 d-block text-center shadow-sm text-decoration-none text-dark h-100">
                    <img src="assets/img/doctor.png" alt="الأطباء" width="56" class="mb-2">
                    <h5>الأطباء</h5>
                    <div class="text-muted small">كل التخصصات</div>
                </a>
            </div>
            <div class="col-6 col-md-3">
                <a href="services/list.php?type=pharmacy" class="service-card p-4 d-block text-center shadow-sm text-decoration-none text-dark h-100">
                    <img src="assets/img/pharmacy.png" alt="الصيدليات" width="56" class="mb-2">
                    <h5>الصيدليات</h5>
                    <div class="text-muted small">صيدليات معتمدة</div>
                </a>
            </div>
            <div class="col-6 col-md-3">
                <a href="services/list.php?type=lab" class="service-card p-4 d-block text-center shadow-sm text-decoration-none text-dark h-100">
                    <img src="assets/img/lab.png" alt="مخابر التحليل" width="56" class="mb-2">
                    <h5>مخابر التحليل</h5>
                    <div class="text-muted small">تحاليل طبية</div>
                </a>
            </div>
            <div class="col-6 col-md-3">
                <a href="services/list.php?type=ambulance" class="service-card p-4 d-block text-center shadow-sm text-decoration-none text-dark h-100">
                    <img src="assets/img/ambulance.png" alt="سيارات الإسعاف" width="56" class="mb-2">
                    <h5>سيارات الإسعاف</h5>
                    <div class="text-muted small">خدمة طارئة</div>
                </a>
            </div>
        </div>
        <div class="row g-4 justify-content-center mt-3">
            <div class="col-6 col-md-3">
                <a href="services/list.php?type=psychologist" class="service-card p-4 d-block text-center shadow-sm text-decoration-none text-dark h-100">
                    <img src="assets/img/psychology.png" alt="مختصون نفسيون" width="56" class="mb-2">
                    <h5>مختصون نفسيون</h5>
                    <div class="text-muted small">دعم نفسي</div>
                </a>
            </div>
            <div class="col-6 col-md-3">
                <a href="services/list.php?type=orthophonist" class="service-card p-4 d-block text-center shadow-sm text-decoration-none text-dark h-100">
                    <img src="assets/img/ortho.png" alt="أرطوفوني" width="56" class="mb-2">
                    <h5>أرطوفوني</h5>
                    <div class="text-muted small">علاج النطق</div>
                </a>
            </div>
            <div class="col-6 col-md-3">
                <a href="services/list.php?type=imaging" class="service-card p-4 d-block text-center shadow-sm text-decoration-none text-dark h-100">
                    <img src="assets/img/imaging.png" alt="مخابر الأشعة" width="56" class="mb-2">
                    <h5>مخابر الأشعة</h5>
                    <div class="text-muted small">راديو/سكانير</div>
                </a>
            </div>
            <div class="col-6 col-md-3">
                <a href="services/list.php?type=physical" class="service-card p-4 d-block text-center shadow-sm text-decoration-none text-dark h-100">
                    <img src="assets/img/imaging.jpg" alt="مؤسسات استشفائية" width="56" class="mb-2">
                    <h5>مراكز العلاج الفيزيائي</h5>
                    <div class="text-muted small">مراكز العلاج الفيزيائي</div>
                </a>
            </div>
        </div>
    </div>
</section>

<!-- قسم عروض المتبرعين بالدم (الجديد فقط) -->
<?php if(count($donors)): ?>
<section class="py-5" style="background: linear-gradient(90deg,#fff4f4 0,#fff8f8 100%);">
    <div class="container">
        <h2 class="text-center text-danger fw-bold mb-4"><i class="bi bi-droplet-half"></i> عروض التبرع بالدم</h2>
        <div class="row g-4 justify-content-center">
            <?php foreach($donors as $d): ?>
                <div class="col-md-4">
                    <div class="p-3 rounded-4 shadow-sm h-100 border border-danger">
                        <div class="fw-bold mb-2 text-danger">
                            متبرع بزمرة (<?=htmlspecialchars($d['blood_type'])?>)
                        </div>
                        <div class="mb-2"><i class="bi bi-person"></i> <?=htmlspecialchars($d['donor_name'])?></div>
                        <div class="mb-2"><i class="bi bi-geo-alt"></i> <?=htmlspecialchars($d['city'])?></div>
                        <div class="mb-2"><?=htmlspecialchars($d['details'])?></div>
                        <div class="text-end mb-2">
                            <span class="badge bg-success">تواصل: <?=htmlspecialchars($d['contact'])?></span>
                        </div>
                        <div class="d-grid gap-2">
                            <?php if(preg_match('/^0[567]/', $d['contact'])): ?>
                                <a href="https://wa.me/213<?=substr(preg_replace('/\D/','',$d['contact']),1)?>" target="_blank" class="btn btn-success btn-sm">
                                    <i class="bi bi-whatsapp"></i> تواصل واتساب
                                </a>
                            <?php endif; ?>
                            <a href="tel:<?=htmlspecialchars($d['contact'])?>" class="btn btn-outline-danger btn-sm">
                                <i class="bi bi-telephone"></i> اتصال هاتفي
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="text-center mt-4">
            <a href="blood_donors.php" class="btn btn-danger btn-lg">شاهد كل العروض</a>
            <a href="blood_donor_add.php" class="btn btn-outline-danger btn-lg ms-2">أضف نفسك كمتبرع</a>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- قسم طلبات الدم (كما هو في صفحتك) -->
<?php
$bloods = $pdo->query("SELECT * FROM blood_requests WHERE is_active=1 ORDER BY 
    FIELD(urgency,'حرجة','مستعجلة','عادية'), created_at DESC LIMIT 6")->fetchAll();
?>
<section class="py-5" style="background: linear-gradient(90deg,#fff4f4 0,#fff8f8 100%);">
    <div class="container">
        <h2 class="text-center text-danger fw-bold mb-4"><i class="bi bi-droplet-half"></i> حالات التبرع بالدم</h2>
        <div class="row g-4 justify-content-center">
            <?php if(!$bloods): ?>
                <div class="alert alert-info">لا يوجد طلبات تبرع بالدم متاحة حالياً.</div>
            <?php else: ?>
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
                            <div class="mb-2"><i class="bi bi-person"></i> <?=htmlspecialchars($b['patient_name'])?></div>
                            <div class="mb-2"><i class="bi bi-geo-alt"></i> <?=htmlspecialchars($b['city'])?></div>
                            <div class="mb-2"><?=htmlspecialchars($b['details'])?></div>
                            <div class="text-end mb-2">
                                <span class="badge bg-success">تواصل: <?=htmlspecialchars($b['contact'])?></span>
                            </div>
                            <div class="d-grid gap-2">
                                <?php if(preg_match('/^0[567]/', $b['contact'])): ?>
                                    <a href="https://wa.me/213<?=substr(preg_replace('/\D/','',$b['contact']),1)?>" target="_blank" class="btn btn-success btn-sm">
                                        <i class="bi bi-whatsapp"></i> تواصل واتساب
                                    </a>
                                <?php endif; ?>
                                <a href="tel:<?=htmlspecialchars($b['contact'])?>" class="btn btn-outline-danger btn-sm">
                                    <i class="bi bi-telephone"></i> اتصال هاتفي
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        <div class="text-center mt-4">
            <a href="blood_request_add.php" class="btn btn-danger btn-lg">أضف طلب تبرع بالدم</a>
        </div>
    </div>
</section>

<!-- مودال التواصل مع صاحب منشور الدم (كما هو) -->
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

<!-- بحث الدواء مباشرة -->
<section class="py-5" style="background: linear-gradient(90deg,#f7fff9 0,#e0f7fa 100%);">
    <div class="container">
        <h2 class="text-center text-success fw-bold mb-4">ابحث عن دواء</h2>
        <form method="get" action="drug_search.php" class="mb-4 w-75 mx-auto">
            <div class="input-group">
                <input type="text" name="q" class="form-control form-control-lg" placeholder="اكتب اسم الدواء..." required>
                <button class="btn btn-success btn-lg" type="submit"><i class="bi bi-search"></i> بحث</button>
            </div>
        </form>
    </div>
</section>

<!-- زر تتبع طلب الدواء للزائر -->
<div class="text-center my-4">
    <button class="btn btn-outline-success btn-lg px-5" data-bs-toggle="modal" data-bs-target="#trackOrderModal">
        تتبع طلب دواء كزائر
    </button>
</div>

<!-- نافذة مودال تتبع الطلب للزائر -->
<div class="modal fade" id="trackOrderModal" tabindex="-1" aria-labelledby="trackOrderModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form id="trackOrderForm" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="trackOrderModalLabel">تتبع طلب دواء (زائر)</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="إغلاق"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
          <label>رقم الهاتف</label>
          <input type="text" name="phone" required class="form-control" placeholder="06xxxxxxxx">
        </div>
        <div id="trackOrderResult"></div>
      </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-success">تتبع الطلب</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إغلاق</button>
      </div>
    </form>
  </div>
</div>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$('#trackOrderForm').on('submit', function(e){
  e.preventDefault();
  $('#trackOrderResult').html('<div class="text-center text-muted">جاري البحث...</div>');
  $.post('patient/track_order_ajax.php', $(this).serialize(), function(res){
    $('#trackOrderResult').html(res.html);
  }, 'json').fail(function(xhr){
    $('#trackOrderResult').html('<div class="text-danger">حدث خطأ أثناء معالجة الطلب!</div>');
  });
});
</script>

<!-- قسم شهادات المرضى (ماذا قالوا عنا) -->
<?php
$stmt = $pdo->query("SELECT t.*, u.name FROM testimonials t JOIN users u ON t.user_id=u.id WHERE t.approved=1 ORDER BY t.created_at DESC LIMIT 3");
$testimonials = $stmt->fetchAll();
?>
<section class="py-5 bg-white">
    <div class="container">
        <h2 class="text-center text-success fw-bold mb-4">ماذا قالوا عنا</h2>
        <div class="row g-4 justify-content-center">
            <?php foreach($testimonials as $t): ?>
                <div class="col-md-4">
                    <div class="p-4 bg-light rounded-4 shadow-sm h-100">
                        <div class="mb-2"><i class="bi bi-quote fs-1 text-success"></i></div>
                        <p class="mb-1"><?=nl2br(htmlspecialchars($t['content']))?></p>
                        <div class="text-end fw-bold mt-3">- <?=htmlspecialchars($t['name'])?><?= $t['city'] ? '، '.htmlspecialchars($t['city']) : ''?></div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <?php if(isset($_SESSION['user_id']) && $_SESSION['role']=='patient'): ?>
        <form action="add_testimonial.php" method="post" class="mt-5">
            <h5 class="mb-3 text-success">هل لديك تجربة جميلة معنا؟ شاركها:</h5>
            <input type="text" name="city" class="form-control mb-2" placeholder="مدينتك (اختياري)">
            <textarea name="content" class="form-control mb-2" placeholder="اكتب رأيك هنا" required></textarea>
            <button class="btn btn-success">إرسال</button>
        </form>
        <?php endif; ?>
    </div>
</section>

<!-- إعلان طبي عشوائي أسفل الصفحة -->
<?php
$ad2 = $pdo->query("SELECT * FROM medical_ads WHERE is_active=1 ORDER BY RAND() LIMIT 1 OFFSET 2")->fetch();
if ($ad2):
?>
<section class="my-4 text-center">
    <a href="<?=htmlspecialchars($ad2['url'])?>" target="_blank" style="text-decoration:none">
        <div class="d-inline-block p-3 bg-white shadow rounded-4" style="min-width:280px;">
            <img src="ads/<?=htmlspecialchars($ad2['image'])?>" alt="" style="height:60px;">
            <h5 class="fw-bold mt-2 mb-1 text-success"><?=htmlspecialchars($ad2['title'])?></h5>
            <div class="small text-muted"><?=htmlspecialchars($ad2['description'])?></div>
            <?php if(!empty($ad2['phone'])): ?>
                <div class="small text-info mt-1"><i class="bi bi-telephone"></i> <?=htmlspecialchars($ad2['phone'])?></div>
            <?php endif; ?>
        </div>
    </a>
</section>
<?php endif; ?>

<!-- دعوة مميزة للتسجيل -->
<section class="cta-section py-5 text-center" style="background: linear-gradient(90deg,#14b98a 0,#38f9d7 100%); color:#fff;">
    <div class="container">
        <h2 class="fw-bold mb-3">جاهز لتجربة صحية رقمية فريدة؟</h2>
        <p class="lead mb-4">سجّل الآن واحجز أول موعد لك أو استفد من استشارة طبية عن بعد!</p>
        <a href="auth/register.php" class="btn btn-light btn-lg fw-bold px-5">سجّل مجاناً الآن</a>
        <a href="contact.php" class="btn btn-outline-light btn-lg fw-bold px-5 ms-2">تواصل معنا</a>
    </div>
</section>

<?php include 'includes/footer.php'; ?>