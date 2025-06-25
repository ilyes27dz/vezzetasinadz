<?php
require_once 'db.php';
session_start();
$q = isset($_GET['q']) ? trim($_GET['q']) : '';

// جلب كل الصيدليات من جدول الخدمات
$pharmacies = $pdo->query("SELECT * FROM services WHERE type='pharmacy' ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);

// جلب كل الأدوية المتوفرة للصيدليات
$drug_pharmacies = [];
if ($q) {
    $stmt = $pdo->prepare("SELECT pharmacy_id, name, price, is_available, id FROM pharmacy_drugs WHERE name LIKE ?");
    $stmt->execute(['%'.$q.'%']);
    foreach($stmt as $row) {
        $drug_pharmacies[$row['pharmacy_id']] = $row;
    }
}

// جلب الأدوية النادرة المطابقة للبحث
$rare_drugs = [];
if ($q) {
    $rare_stmt = $pdo->prepare(
        "SELECT r.*, s.name AS pharmacy_name
         FROM rare_drugs r
         LEFT JOIN services s ON r.pharmacy_id=s.id
         WHERE r.name LIKE ?
         ORDER BY r.added_at DESC"
    );
    $rare_stmt->execute(['%'.$q.'%']);
    $rare_drugs = $rare_stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
<?php include 'includes/header.php'; ?>
<div class="container py-5">
    <!-- زر العودة -->
    <div class="mb-4">
        <a href="index.php" class="btn btn-outline-secondary me-2 mb-2"><i class="bi bi-arrow-right"></i> العودة للرئيسية</a>
        <a href="services/list.php?type=pharmacy" class="btn btn-outline-success me-2 mb-2">كل الصيدليات</a>
        <a href="contact.php" class="btn btn-outline-info mb-2">تواصل معنا</a>
    </div>
    <h2 class="mb-4 text-success"><i class="bi bi-capsule"></i> نتائج البحث عن دواء</h2>
    <form method="get" action="drug_search.php" class="mb-4 w-75 mx-auto">
        <div class="input-group">
            <input type="text" name="q" class="form-control form-control-lg" placeholder="اكتب اسم الدواء..." value="<?=htmlspecialchars($q)?>" required>
            <button class="btn btn-success btn-lg" type="submit"><i class="bi bi-search"></i> بحث</button>
        </div>
    </form>
    <?php if($q): ?>
        <div class="table-responsive">
        <table class="table table-hover align-middle text-center">
            <thead class="table-success">
                <tr>
                    <th>الصيدلية</th>
                    <th>العنوان</th>
                    <th>الهاتف</th>
                    <th>توفر الدواء</th>
                    <th>السعر</th>
                    <th>طلب الدواء</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach($pharmacies as $ph):
                $has = isset($drug_pharmacies[$ph['id']]) && $drug_pharmacies[$ph['id']]['is_available'];
                $drug = $has ? $drug_pharmacies[$ph['id']] : null;
                ?>
                <tr class="<?= $has ? 'table-light' : 'table-danger' ?>">
                    <td class="fw-bold"><?=htmlspecialchars($ph['name'])?>
                        <?php if($ph['wilaya']): ?>
                            <span class="badge bg-secondary"><?=htmlspecialchars($ph['wilaya'])?></span>
                        <?php endif; ?>
                    </td>
                    <td><?= !empty($ph['address']) ? htmlspecialchars($ph['address']) : '<span class="text-muted">---</span>' ?></td>
                    <td>
                        <?php if(!empty($ph['phone'])): ?>
                            <span class="badge bg-info text-dark"><i class="bi bi-telephone"></i> <?=htmlspecialchars($ph['phone'])?></span>
                        <?php else: ?>
                            <span class="text-muted">---</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if($has): ?>
                            <span class="badge bg-success px-3"><i class="bi bi-check-circle-fill"></i> متوفر</span>
                        <?php else: ?>
                            <span class="badge bg-danger px-3"><i class="bi bi-x-circle-fill"></i> غير متوفر</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if($has && $drug['price']): ?>
                            <span class="badge bg-warning text-dark"><?= $drug['price'] ?> دج</span>
                        <?php else: ?>
                            <span class="text-muted">---</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if($has): ?>
                            <button class="btn btn-outline-primary btn-sm"
                                data-bs-toggle="modal"
                                data-bs-target="#orderDrugModal"
                                data-pharmacy-id="<?= $ph['id'] ?>"
                                data-pharmacy-name="<?= htmlspecialchars($ph['name']) ?>"
                                data-drug-name="<?= htmlspecialchars($drug['name']) ?>"
                                data-drug-price="<?= $drug['price'] ?>"
                            >
                                <i class="bi bi-cart-plus"></i> طلب الدواء
                            </button>
                        <?php else: ?>
                            <button class="btn btn-outline-secondary btn-sm" disabled>
                                <i class="bi bi-cart-x"></i>
                            </button>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        </div>

        <!-- جدول الأدوية النادرة -->
        <hr class="my-5">
        <h3 class="text-danger text-center mb-3"><i class="bi bi-exclamation-circle"></i> نتائج الأدوية النادرة</h3>
        <div class="table-responsive">
            <table class="table table-bordered align-middle text-center">
                <thead class="table-warning">
                    <tr>
                        <th>#</th>
                        <th>الصورة</th>
                        <th>اسم الدواء</th>
                        <th>الفئة</th>
                        <th>ملاحظات</th>
                        <th>الصيدلية المتوفر بها</th>
                        <th>تاريخ الإضافة</th>
                        <th>طلب دواء نادر</th>
                    </tr>
                </thead>
                <tbody>
                <?php if($rare_drugs && count($rare_drugs)): foreach($rare_drugs as $i => $drug): ?>
                    <tr>
                        <td><?= $i+1 ?></td>
                        <td>
                            <?php if(!empty($drug['photo'])): ?>
                                <img src="uploads/rare_drugs/<?=htmlspecialchars($drug['photo'])?>" alt="صورة الدواء" style="width:56px; height:56px; border-radius:8px; object-fit:cover; border:2px solid #17a2b8;">
                            <?php else: ?>
                                <span class="text-muted">لا توجد</span>
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($drug['name']) ?></td>
                        <td><?= htmlspecialchars($drug['category']) ?></td>
                        <td><?= htmlspecialchars($drug['notes']) ?></td>
                        <td><?= $drug['pharmacy_name'] ? htmlspecialchars($drug['pharmacy_name']) : '<span class="text-danger">غير متوفر</span>' ?></td>
                        <td><?= htmlspecialchars($drug['added_at']) ?></td>
                        <td>
                            <a href="rare_drug_request.php?drug_id=<?=urlencode($drug['id'])?>" class="btn btn-outline-danger btn-sm">
                                <i class="bi bi-bag-plus"></i> طلب نادر
                            </a>
                        </td>
                    </tr>
                <?php endforeach; else: ?>
                    <tr>
                        <td colspan="8" class="text-muted">لا توجد نتائج للأدوية النادرة بهذا الاسم.</td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<!-- نافذة الطلب المنبثقة (Bootstrap Modal) -->
<div class="modal fade" id="orderDrugModal" tabindex="-1" aria-labelledby="orderDrugModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form class="modal-content" id="orderDrugForm">
      <div class="modal-header">
        <h5 class="modal-title" id="orderDrugModalLabel">طلب دواء</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="إغلاق"></button>
      </div>
      <div class="modal-body">
        <div id="orderDrugAlert"></div>
        <input type="hidden" name="pharmacy_id" id="modal_pharmacy_id">
        <input type="hidden" name="drug_name" id="modal_drug_name">
        <div class="mb-2">
            <b>الصيدلية:</b> <span id="modal_pharmacy_name"></span>
        </div>
        <div class="mb-2">
            <b>اسم الدواء:</b> <span id="modal_drug_name_show"></span>
        </div>
        <div class="mb-2">
            <b>السعر:</b> <span id="modal_drug_price"></span> دج
        </div>
        <?php if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'patient'): ?>
        <div class="mb-3">
            <label class="form-label">البريد الإلكتروني</label>
            <input type="email" name="email" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">رقم الهاتف</label>
            <input type="text" name="phone" class="form-control" required>
        </div>
        <?php endif; ?>
        <div class="mb-3">
            <label class="form-label">طريقة الاستلام</label><br>
            <input type="radio" name="delivery" value="pickup" checked> أستلم من الصيدلية
            <input type="radio" name="delivery" value="delivery" class="ms-3"> توصيل للمنزل (+300 دج)
        </div>
        <div class="mb-3">
            <label class="form-label">ملاحظات (اختياري)</label>
            <textarea name="notes" class="form-control" rows="2"></textarea>
        </div>
      </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-success w-100">إرسال الطلب</button>
      </div>
    </form>
  </div>
</div>

<script>
var orderDrugModal = document.getElementById('orderDrugModal');
orderDrugModal.addEventListener('show.bs.modal', function (event) {
    var button = event.relatedTarget;
    var pharmacy_id = button.getAttribute('data-pharmacy-id');
    var pharmacy_name = button.getAttribute('data-pharmacy-name');
    var drug_name = button.getAttribute('data-drug-name');
    var drug_price = button.getAttribute('data-drug-price');
    //
    document.getElementById('modal_pharmacy_id').value = pharmacy_id;
    document.getElementById('modal_pharmacy_name').innerText = pharmacy_name;
    document.getElementById('modal_drug_name').value = drug_name;
    document.getElementById('modal_drug_name_show').innerText = drug_name;
    document.getElementById('modal_drug_price').innerText = drug_price;
    document.getElementById('orderDrugAlert').innerHTML = '';
});
document.getElementById('orderDrugForm').addEventListener('submit', function(e){
    e.preventDefault();
    let form = this;
    let formData = new FormData(form);
    fetch('patient/order_drug_ajax.php', {
        method: 'POST',
        body: formData
    }).then(res=>res.json())
      .then(data=>{
        if(data.success){
            document.getElementById('orderDrugAlert').innerHTML = '<div class="alert alert-success">'+data.message+'</div>';
            form.reset();
            setTimeout(()=>{ var modal = bootstrap.Modal.getInstance(orderDrugModal); modal.hide(); }, 2000);
        } else {
            document.getElementById('orderDrugAlert').innerHTML = '<div class="alert alert-danger">'+data.message+'</div>';
        }
      });
});
</script>
<?php include 'includes/footer.php'; ?>