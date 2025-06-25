<?php
// ملف الاتصال بقاعدة البيانات

$host = 'localhost';
$db   = 'medical_platform'; // اسم قاعدة البيانات التي أنشأتها
$user = 'root';             // اسم مستخدم قاعدة البيانات (غالبا root في السيرفر المحلي)
$pass = '';                 // كلمة المرور (اتركها فارغة في XAMPP/WAMP غالباً)
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // إظهار الأخطاء بوضوح
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // جلب النتائج كمصفوفة عادية
    PDO::ATTR_EMULATE_PREPARES   => false,                  // حماية من حقن SQL
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    die('فشل الاتصال بقاعدة البيانات: ' . $e->getMessage());
}
?>