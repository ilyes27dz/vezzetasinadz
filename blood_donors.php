<?php
require_once 'db.php';
// فلترة
$filter_city = $_GET['city'] ?? '';
$filter_blood = $_GET['blood_type'] ?? '';
$query = "SELECT * FROM blood_donors WHERE is_active=1";
$params = [];
if($filter_city) { $query .= " AND city=?"; $params[] = $filter_city; }
if($filter_blood) { $query .= " AND blood_type=?"; $params[] = $filter_blood; }
$query .= " ORDER BY created_at DESC";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$donors = $stmt->fetchAll();
$cities = $pdo->query("SELECT DISTINCT city FROM blood_donors WHERE is_active=1 ORDER BY city")->fetchAll(PDO::FETCH_COLUMN);
$blood_types = $pdo->query("SELECT DISTINCT blood_type FROM blood_donors WHERE is_active=1 ORDER BY blood_type")->fetchAll(PDO::FETCH_COLUMN);
include 'includes/header.php';
?>
<div class="container py-5">
    <div class="mb-4">
        <a href="index.php" class="btn btn-outline-secondary me-2 mb-2"><i class="bi bi-arrow-right"></i> العودة للرئيسية</a>
        <a href="blood_donor_add.php" class="btn btn-danger mb-2">أضف نفسك كمتبرع</a>
    </div>
    <div class="alert alert-info text-center mb-4">
        يمكنك الفلترة حسب المدينة أو الزمرة. تواصل مباشرة مع المتبرع عبر الهاتف أو الواتساب بدون وسيط!
    </div>
    <h2 class="mb-4 text-danger"><i class="bi bi-droplet-half"></i> عروض التبرع بالدم</h2>
    <form class="row g-2 mb-4">
        <div class="col-md-4">
            <select class="form-select" name="city">
                <option value="">كل المدن</option>
                <?php foreach($cities as $c): ?>
                    <option value="<?=htmlspecialchars($c)?>" <?=($filter_city==$c?'selected':'')?>><?=htmlspecialchars($c)?></option>
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
        <?php if(!$donors): ?>
            <div class="col-12 text-center"><div class="alert alert-info">لا يوجد متبرعين حسب الفلتر المختار حالياً.</div></div>
        <?php endif; ?>
    </div>
</div>
<?php include 'includes/footer.php'; ?>