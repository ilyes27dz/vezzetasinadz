<?php
require_once 'db.php';
$success = '';
if($_SERVER['REQUEST_METHOD']=='POST'){
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $subject = trim($_POST['subject']);
    $message = trim($_POST['message']);
    if($name && $email && $subject && $message){
        $stmt = $pdo->prepare("INSERT INTO contact_messages(name,email,subject,message) VALUES(?,?,?,?)");
        $stmt->execute([$name,$email,$subject,$message]);
        $success = "تم إرسال رسالتك بنجاح! سنرد عليك قريبًا.";
        $_POST = [];
    }
}
include 'includes/header.php';
?>
<div class="container py-5">
    <!-- زر العودة -->
    <div class="mb-4">
        <a href="index.php" class="btn btn-outline-secondary me-2 mb-2"><i class="bi bi-arrow-right"></i> العودة للرئيسية</a>
        <a href="services/list.php?type=doctor" class="btn btn-outline-success me-2 mb-2">كل الخدمات</a>
    </div>
    <div class="row justify-content-center">
        <div class="col-md-7">
            <div class="card p-4 shadow">
                <h2 class="text-success fw-bold mb-4 text-center"><i class="bi bi-chat-dots me-2"></i> تواصل معنا</h2>
                <?php if($success): ?><div class="alert alert-success"><?=$success?></div><?php endif; ?>
                <form method="post">
                    <div class="mb-3">
                        <label class="form-label">الاسم الكامل</label>
                        <input type="text" class="form-control" name="name" required value="<?=htmlspecialchars($_POST['name'] ?? '')?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">البريد الإلكتروني</label>
                        <input type="email" class="form-control" name="email" required value="<?=htmlspecialchars($_POST['email'] ?? '')?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">الموضوع</label>
                        <input type="text" class="form-control" name="subject" required value="<?=htmlspecialchars($_POST['subject'] ?? '')?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">رسالتك</label>
                        <textarea class="form-control" name="message" rows="5" required><?=htmlspecialchars($_POST['message'] ?? '')?></textarea>
                    </div>
                    <button type="submit" class="btn btn-main w-100 py-2">إرسال الرسالة</button>
                </form>
            </div>
        </div>
    </div>
</div>
<?php include 'includes/footer.php'; ?>