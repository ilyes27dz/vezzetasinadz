<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'patient') {
    header("Location: ../auth/login.php"); exit;
}
require_once __DIR__ . '/../db.php'
;

// إشعارات المستخدم
$notif_count = 0;
if (isset($_SESSION['user_id'])) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id=? AND is_read=FALSE
");
    $stmt->execute([$_SESSION['user_id']]);
    $notif_count = $stmt->fetchColumn();
}

// بيانات المستخدم
$stmt = $pdo->prepare("SELECT * FROM users WHERE id=?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

// جلب المواعيد
$stmt = $pdo->prepare("SELECT a.*, u.name AS doctor_name FROM appointments a 
    JOIN doctors d ON a.doctor_id=d.id 
    JOIN users u ON d.user_id=u.id 
    WHERE a.patient_id=?
    ORDER BY a.date DESC");
$stmt->execute([$_SESSION['user_id']]);
$appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);

// جلب الطلبيات
$stmt = $pdo->prepare("SELECT * FROM drug_orders WHERE patient_id=? ORDER BY created_at DESC");
$stmt->execute([$_SESSION['user_id']]);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

// جلب الأدوية النادرة (لعرضها في كارت منفصل)
$rare_drugs_count = $pdo->query("SELECT COUNT(*) FROM rare_drugs")->fetchColumn();

// دالة شارات حالات المواعيد
function get_status_badge($status) {
    $status = strtolower($status);
    if ($status == 'confirmed') {
        return '<span class="badge bg-success"><i class="bi bi-check-circle"></i> مؤكد</span>';
    } elseif ($status == 'pending') {
        return '<span class="badge bg-secondary"><i class="bi bi-clock-history"></i> قيد الدراسة</span>';
    } elseif ($status == 'cancelled' || $status == 'canceled') {
        return '<span class="badge bg-danger"><i class="bi bi-x-circle"></i> ملغى</span>';
    } elseif ($status == 'completed') {
        return '<span class="badge bg-info text-white"><i class="bi bi-check2-square"></i> مكتمل</span>';
    }
    return '<span class="badge bg-light text-dark">'.htmlspecialchars($status).'</span>';
}
?>
<?php include __DIR__ . '/../../includes/header.php'	; ?>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<style>
body {background: #f7fcff;}
.dashboard-main {
    max-width: 1150px;
    margin: 38px auto 0 auto;
    background: #f8fff6;
    border-radius: 28px;
    box-shadow: 0 8px 42px #53c2a215;
    padding: 38px 15px 36px 15px;
}
/* ========== بطاقة الملف الشخصي ========== */
.profile-card {
    display: flex;
    align-items: center;
    gap: 20px;
    margin-bottom: 35px;
    background: linear-gradient(100deg, #f4fff5 65%, #e4f3ff 100%);
    border-radius: 22px;
    box-shadow: 0 2px 18px #b6e7c76d;
    padding: 15px 16px 15px 8px;
}
.profile-img {
    width: 82px; height: 82px;
    border-radius: 50%;
    border: 2.5px solid #36c66f;
    object-fit: cover; background: #fff;
}
.profile-info h4 { margin-bottom: 2px; font-weight: 800; color: #388e3c; }
.profile-info .small { color: #888; font-size: 0.96em; }
.profile-card .profile-actions {
    margin-right: auto;
    display: flex;
    gap: 7px;
}

/* ========== تحسينات الجوال ========== */
@media (max-width: 600px) {
    .dashboard-main {
        margin: 12px 0 0 0;
        border-radius: 8px;
        padding: 8px 2px 12px 2px;
    }
    .profile-card {
        flex-direction: column;
        align-items: center;
        text-align: center;
        gap: 12px;
        padding: 10px 3px 10px 3px;
    }
    .profile-img { width: 62px; height: 62px;}
    .profile-card .profile-actions {
        margin: 0;
        justify-content: center;
        width: 100%;
    }
}

/* ========== بطاقات الخدمات السريعة ========== */
.cards-row {
    display: flex;
    flex-wrap: wrap;
    gap: 1.3rem;
    margin-bottom: 30px;
    justify-content: center;
}
.quick-card {
    flex: 1 1 210px;
    background: linear-gradient(112deg, #f9fcfa 70%, #e8f5e9 100%);
    border-radius: 16px;
    box-shadow: 0 2px 16px #b6e7c7;
    padding: 22px 6px 16px 6px;
    text-align: center;
    border: 2px solid #36c66f12;
    transition: box-shadow .14s, transform .11s;
    color: #247641;
    min-width: 170px;
    max-width: 300px;
    position: relative;
}
.quick-card:hover {
    box-shadow: 0 10px 32px #36c66f1f;
    transform: scale(1.03);
    background: #f2fff7;
    color: #144c28;
}
.quick-card i { font-size: 2.55rem; margin-bottom: 8px; }
.quick-card h5 { font-size: 1.03rem; font-weight: bold; margin-top: 8px; margin-bottom: 2px; color: #277c42; }
.quick-card .desc { color: #777; font-size: .96rem; margin-bottom: 0; }

@media (max-width: 900px){
    .cards-row { flex-direction: column; gap: 16px; }
    .quick-card { max-width: 100%; min-width: 0; width: 100%; }
}

/* ========== بطاقات المتابعة ========== */
.followup-cards-row {
    display: flex;
    gap: 2.2rem;
    margin-bottom: 0;
    flex-wrap: wrap;
    justify-content: center;
}
.followup-card-special {
    flex: 1 1 350px;
    background: linear-gradient(122deg, #f7f8ff 65%, #dafcee 100%);
    border-radius: 22px;
    padding: 0;
    box-shadow: 0 7px 42px #53c2a245, 0 1.5px 8px #44c3a4a1;
    border: none;
    transition: box-shadow .13s, transform .12s;
    position: relative;
    overflow: hidden;
    min-width: 250px;
    max-width: 470px;
    margin-bottom: 18px;
    display: flex;
    flex-direction: column;
    justify-content: flex-start;
}
.followup-card-special .card-header {
    background: linear-gradient(90deg, #36c66f 80%, #53c2a2 100%);
    color: #fff;
    font-weight: 700;
    font-size: 1.18rem;
    border-radius: 22px 22px 0 0;
    padding: 18px 12px 11px 12px;
    border: none;
    box-shadow: 0 4px 16px #36c66f27;
    display: flex;
    align-items: center;
    gap: 8px;
    letter-spacing: 0.01em;
}
.followup-card-special .card-header i { font-size: 1.9rem; margin-left: 7px; }
.followup-card-special .card-body {
    padding: 18px 9px 12px 9px;
    min-height: 120px;
    background: #fafdff;
    width: 100%;
}
.followup-card-special .timeline-list { list-style: none; padding: 0; margin: 0; }
.followup-card-special .timeline-list li {
    position: relative;
    margin-bottom: 18px;
    padding-bottom: 10px;
    padding-left: 0;
    border-right: 3px solid #36c66f26;
    margin-right: 7px;
    min-height: 30px;
}
.followup-card-special .timeline-list li:last-child {
    margin-bottom: 20px;
    padding-bottom: 20px;
    border-right: none;
}
.followup-card-special .timeline-dot {
    position: absolute;
    right: -13px;
    top: 6px;
    width: 11px; height: 11px; background: #36c66f; border-radius:50%; border:2px solid #fff;
    box-shadow: 0 1px 4px #36c66f60;
}
.followup-card-special .timeline-date { font-size: .92rem; color: #36c66f; font-weight: bold; margin-left: 5px; }
.followup-card-special .timeline-title {
    font-size: 1rem;
    color: #111;
    font-weight: 600;
    margin-bottom: 6px;
    margin-top: 2px;
    word-break: break-word;
    display: block;
}
.followup-card-special .timeline-status {
    display: block;
    margin: 5px 0 0 0;
}
.followup-card-special .card-footer {
    background: linear-gradient(90deg, #36c66f 80%, #53c2a2 100%);
    color: #fff;
    text-align: center;
    font-weight: 600;
    font-size: 1.05rem;
    letter-spacing: .01em;
    border-radius: 0 0 22px 22px;
    padding: 8px 0 8px 0;
    border: none;
    width: 100%;
    min-height: 48px;
    margin-top: 0 !important;
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 2;
    position: relative;
}
.followup-card-special .card-footer a {
    display: block;
    color: #fff !important;
    background: none !important;
    padding: 13px 0 8px 0;
    font-size: 1.08rem;
    border-radius: 6px;
    text-align: center;
    width: 100%;
    transition: background 0.2s, color 0.2s;
    text-decoration: none;
    z-index: 2;
    position: relative;
}
.followup-card-special .card-footer a:hover {
    background: #44c3a4 !important;
    color: #fff !important;
}

@media (max-width: 900px){
    .followup-cards-row { flex-direction:column; gap:1.2rem; }
    .followup-card-special { max-width: 100%; min-width: 0; }
}
@media (max-width: 600px){
    .followup-card-special { border-radius: 10px; }
    .followup-card-special .card-header { border-radius: 10px 10px 0 0; padding: 11px 7px 7px 7px; font-size: 1rem;}
    .followup-card-special .card-body { padding: 7px 3px 4px 3px; }
    .followup-card-special .card-footer { border-radius: 0 0 10px 10px; padding: 6px 0 6px 0; font-size: .98rem;}
    .followup-card-special .card-footer a { font-size: .97rem; padding: 13px 0; }
    .followup-card-special .timeline-title { font-size: .93rem; margin-bottom: 8px; }
}
</style>

<div class="dashboard-main">

    <!-- بيانات المستخدم -->
    <div class="profile-card">
        <img src="<?= (!empty($user['photo']) ? '../uploads/' . htmlspecialchars($user['photo']) : 'https://ui-avatars.com/api/?name=' . urlencode(isset($user['name']) ? $user['name'] : 'مريض') . '&background=36c66f&color=fff&rounded=true') ?>"
             class="profile-img" alt="صورة المريض">
        <div class="profile-info">
            <h4>مرحباً <?=htmlspecialchars($user['name'])?> 👋</h4>
            <div class="small"><?=htmlspecialchars($user['email'])?></div>
        </div>
        <div class="profile-actions">
            <a href="profile.php" class="btn btn-outline-success btn-sm"><i class="bi bi-person"></i> تعديل الملف</a>
            <a href="../auth/logout.php" class="btn btn-outline-danger btn-sm"><i class="bi bi-box-arrow-right"></i> خروج</a>
        </div>
    </div>

    <!-- بطاقات الخدمات السريعة -->
    <div class="cards-row mb-5">
        <a href="book_appointment.php" class="quick-card">
            <i class="bi bi-calendar-plus text-success"></i>
            <h5>حجز موعد جديد</h5>
            <div class="desc">اختر طبيبك وحدد الوقت</div>
        </a>
        <a href="consultation.php" class="quick-card">
            <i class="bi bi-chat-text text-primary"></i>
            <h5>استشارة طبية سريعة</h5>
            <div class="desc">اطرح سؤالك على الطبيب مباشرة</div>
        </a>
        <a href="prescription.php" class="quick-card">
            <i class="bi bi-file-earmark-medical text-warning"></i>
            <h5>طلب وصفة طبية</h5>
            <div class="desc">احصل على وصفة إلكترونية</div>
        </a>
        <a href="order_drug.php" class="quick-card">
            <i class="bi bi-capsule text-danger"></i>
            <h5>طلب دواء</h5>
            <div class="desc">اطلب الدواء حتى باب بيتك</div>
        </a>
        <a href="rare_drugs.php" class="quick-card position-relative" style="border:2px solid #f44336;">
            <i class="bi bi-exclamation-circle text-danger"></i>
            <h5>الأدوية النادرة</h5>
            <div class="desc">قائمة الأدوية غير المتوفرة بكثرة</div>
            <?php if($rare_drugs_count): ?>
                <span style="position:absolute; top:10px; left:12px;" class="badge bg-danger"><?= $rare_drugs_count ?></span>
            <?php endif; ?>
        </a>
        <a href="services.php" class="quick-card">
            <i class="bi bi-list-ul text-secondary"></i>
            <h5>تصفح جميع الخدمات</h5>
            <div class="desc">كل مقدمي الخدمات الصحية</div>
        </a>
        <a href="../blood_donors.php" class="quick-card">
            <i class="bi bi-droplet-half text-danger"></i>
            <h5>عروض التبرع بالدم</h5>
            <div class="desc">ابحث عن متبرعين بزمرة دمك مباشرة</div>
        </a>
        <a href="../blood_donor_add.php" class="quick-card">
            <i class="bi bi-plus-circle text-danger"></i>
            <h5>سجّل نفسك كمتبرع دم</h5>
            <div class="desc">ساهم في إنقاذ حياة الآخرين</div>
        </a>
        <a href="subscribe.php" class="quick-card">
            <i class="bi bi-credit-card-2-back-fill"></i>
            <h5>الإشتراك بالبطاقة الذهبية</h5>
            <div class="desc">استفد من مزايا إضافية وخصومات حصرية</div>
        </a>
    </div>
    

    <!-- بطاقات المتابعة المميزة -->
    <div class="followup-cards-row">

        <!-- بطاقة تتبع المواعيد -->
        <div class="followup-card-special">
            <div class="card-header">
                <i class="bi bi-calendar2-week"></i>
                تتبع مواعيدي الطبية
            </div>
            <div class="card-body">
                <?php if($appointments): ?>
                    <ul class="timeline-list">
                        <?php foreach(array_slice($appointments,0,5) as $app): ?>
                            <li>
                                <span class="timeline-dot"></span>
                                <div class="timeline-date"><i class="bi bi-clock-history"></i> <?=htmlspecialchars($app['date'])?></div>
                                <div class="timeline-title"><i class="bi bi-person-badge"></i> د. <?=htmlspecialchars($app['doctor_name'])?></div>
                                <div class="timeline-status"><?= get_status_badge($app['status']) ?></div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <div class="text-muted text-center pt-2">لا توجد مواعيد بعد.</div>
                <?php endif; ?>
            </div>
            <div class="card-footer">
                <a href="appointments.php" class="text-white text-decoration-none"><i class="bi bi-eye"></i> عرض كل المواعيد</a>
            </div>
        </div>

        <!-- بطاقة تتبع طلبيات الدواء -->
        <div class="followup-card-special">
            <div class="card-header">
                <i class="bi bi-truck"></i>
                تتبع طلبيات الدواء
            </div>
            <div class="card-body">
                <?php if($orders): ?>
                    <ul class="timeline-list">
                        <?php foreach(array_slice($orders,0,5) as $order): ?>
                            <li>
                                <span class="timeline-dot"></span>
                                <div class="timeline-date"><i class="bi bi-calendar-event"></i> <?=htmlspecialchars($order['created_at'])?></div>
                                <div class="timeline-title"><?= htmlspecialchars($order['drug_name'] ?? 'دواء') ?></div>
                                <div class="timeline-status">
                                    <?php
                                        $status = strtolower($order['status']);
                                        if($status == 'pending') echo '<span class="badge bg-secondary">قيد المعالجة</span>';
                                        elseif($status == 'shipped') echo '<span class="badge bg-info">تم الشحن</span>';
                                        elseif($status == 'delivered') echo '<span class="badge bg-success">تم التوصيل</span>';
                                        else echo '<span class="badge bg-light text-dark">'.htmlspecialchars($order['status']).'</span>';
                                    ?>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <div class="text-muted text-center pt-2">لا توجد طلبيات بعد.</div>
                <?php endif; ?>
            </div>
            <div class="card-footer">
                <a href="orders.php" class="text-white text-decoration-none"><i class="bi bi-eye"></i> عرض كل الطلبيات</a>
            </div>
        </div>
    </div>
</div>
<?php include __DIR__ . '/../../includes/footer.php'	; ?>