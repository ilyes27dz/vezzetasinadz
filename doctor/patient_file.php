<?php
session_start();
if(!isset($_SESSION['user_id']) || $_SESSION['role']!=='doctor' || !isset($_GET['id'])){header("Location: ../auth/login.php");exit;}
require_once '../db.php';
$patient_id = intval($_GET['id']);

$stmt = $pdo->prepare("SELECT name, email FROM users WHERE id = ?");
$stmt->execute([$patient_id]);
$patient = $stmt->fetch(PDO::FETCH_ASSOC);

$stmt = $pdo->prepare("SELECT * FROM consultations WHERE patient_id = ? ORDER BY created_at DESC");
$stmt->execute([$patient_id]);
$consults = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $pdo->prepare("SELECT * FROM prescriptions WHERE patient_id = ? ORDER BY created_at DESC");
$stmt->execute([$patient_id]);
$prescs = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<?php include '../includes/header.php'; ?>
<div class="container py-5">
    <a href="patients.php" class="btn btn-light mb-3"><i class="bi bi-arrow-right"></i> رجوع لملفات المرضى</a>
    <div class="card shadow-lg mb-4">
        <div class="card-body">
            <div class="d-flex align-items-center">
                <div>
                    <i class="bi bi-person-circle" style="font-size:3rem;color:#388e3c"></i>
                </div>
                <div class="ms-3">
                    <h3 class="mb-1 text-success"><?= htmlspecialchars($patient['name']) ?></h3>
                    <p class="mb-0"><i class="bi bi-envelope"></i> <?= htmlspecialchars($patient['email']) ?></p>
                </div>
            </div>
        </div>
    </div>
    <div class="row g-4">
        <div class="col-md-6">
            <div class="card border-primary shadow-sm">
                <div class="card-header bg-primary text-white">
                    <i class="bi bi-question-circle"></i> الاستشارات السابقة
                </div>
                <ul class="list-group list-group-flush">
                    <?php foreach($consults as $c): ?>
                        <li class="list-group-item">
                            <div>
                                <span class="badge bg-secondary"><?= htmlspecialchars($c['created_at']) ?></span>
                                <p class="mb-1 mt-2"><?= nl2br(htmlspecialchars($c['message'])) ?></p>
                                <?php if ($c['reply']): ?>
                                    <div class="alert alert-success p-2 mt-2"><strong>رد الطبيب:</strong><br><?= nl2br(htmlspecialchars($c['reply'])) ?></div>
                                <?php endif; ?>
                            </div>
                        </li>
                    <?php endforeach; ?>
                    <?php if(empty($consults)): ?>
                        <li class="list-group-item text-center text-muted">لا يوجد استشارات.</li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card border-success shadow-sm">
                <div class="card-header bg-success text-white">
                    <i class="bi bi-file-earmark-medical"></i> الوصفات الطبية
                </div>
                <ul class="list-group list-group-flush">
                    <?php foreach($prescs as $p): ?>
                        <li class="list-group-item">
                            <div>
                                <span class="badge bg-secondary"><?= htmlspecialchars($p['created_at']) ?></span>
                                <p class="mb-1 mt-2"><?= nl2br(htmlspecialchars($p['notes'])) ?></p>
                                <?php if ($p['doctor_notes']): ?>
                                    <div class="alert alert-info p-2 mt-2"><strong>ملاحظات الطبيب:</strong><br><?= nl2br(htmlspecialchars($p['doctor_notes'])) ?></div>
                                <?php endif; ?>
                                <?php if ($p['file']): ?>
                                    <a href="../uploads/<?= htmlspecialchars($p['file']) ?>" class="btn btn-outline-success btn-sm mt-2" target="_blank"><i class="bi bi-download"></i> تحميل الملف</a>
                                <?php endif; ?>
                            </div>
                        </li>
                    <?php endforeach; ?>
                    <?php if(empty($prescs)): ?>
                        <li class="list-group-item text-center text-muted">لا توجد وصفات.</li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </div>
</div>
<?php include '../includes/footer.php'; ?>