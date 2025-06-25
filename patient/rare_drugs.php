<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'patient') { header("Location: ../auth/login.php"); exit; }
require_once '../db.php';

$rare_drugs = $pdo->query(
    "SELECT r.*, s.name AS pharmacy_name 
     FROM rare_drugs r
     LEFT JOIN services s ON r.pharmacy_id=s.id
     ORDER BY r.added_at DESC"
)->fetchAll(PDO::FETCH_ASSOC);
?>
<?php include '../includes/header.php'; ?>
<div class="container py-5">
    <a href="index.php" class="btn btn-outline-secondary mb-3"><i class="bi bi-arrow-right"></i> رجوع</a>
    <a href="request_rare_drug.php" class="btn btn-danger mb-3"><i class="bi bi-bag-plus"></i> طلب دواء نادر</a>
    <a href="my_rare_orders.php" class="btn btn-info mb-3"><i class="bi bi-eye"></i> تتبع طلباتي للأدوية النادرة</a>
    <h3 class="mb-4 text-danger text-center"><i class="bi bi-exclamation-circle"></i> قائمة الأدوية النادرة</h3>
    <div class="card p-3">
        <div class="table-responsive">
            <table class="table table-bordered align-middle text-center">
                <thead class="table-success">
                    <tr>
                        <th>#</th>
                        <th>الصورة</th>
                        <th>اسم الدواء</th>
                        <th>الفئة</th>
                        <th>ملاحظات</th>
                        <th>الصيدلية المتوفر بها</th>
                        <th>تاريخ الإضافة</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach($rare_drugs as $i => $drug): ?>
                    <tr>
                        <td><?= $i+1 ?></td>
                        <td>
                            <?php if(!empty($drug['photo'])): ?>
                                <img src="../uploads/rare_drugs/<?=htmlspecialchars($drug['photo'])?>" alt="صورة الدواء" style="width:56px; height:56px; border-radius:8px; object-fit:cover; border:2px solid #17a2b8;">
                            <?php else: ?>
                                <span class="text-muted">لا توجد</span>
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($drug['name']) ?></td>
                        <td><?= htmlspecialchars($drug['category']) ?></td>
                        <td><?= htmlspecialchars($drug['notes']) ?></td>
                        <td><?= $drug['pharmacy_name'] ? htmlspecialchars($drug['pharmacy_name']) : '<span class="text-danger">غير متوفر</span>' ?></td>
                        <td><?= htmlspecialchars($drug['added_at']) ?></td>
                    </tr>
                <?php endforeach; if(empty($rare_drugs)): ?>
                    <tr><td colspan="7" class="text-muted">لا توجد أدوية نادرة حالياً.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php include '../includes/footer.php'; ?>