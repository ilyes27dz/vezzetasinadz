<?php
// ملف الاتصال بقاعدة البيانات PostgreSQL

// قراءة البيانات من Environment Variables (للأمان في Vercel)
$host = getenv('DB_HOST') ?: 'db.dgbibiepsmbopwjbtoap.supabase.co';
$port = getenv('DB_PORT') ?: '5432';
$db   = getenv('DB_NAME') ?: 'postgres';
$user = getenv('DB_USER') ?: 'postgres';
$pass = getenv('DB_PASSWORD') ?: '123456'; // ضع كلمة المرور هنا مؤقتاً للاختبار المحلي
$charset = 'utf8';

// DSN لـ PostgreSQL
$dsn = "pgsql:host=$host;port=$port;dbname=$db;options='--client_encoding=$charset'";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    die('فشل الاتصال بقاعدة البيانات: ' . $e->getMessage());
}
?>
<?php
// ملف الاتصال بقاعدة البيانات PostgreSQL

// قراءة البيانات من Environment Variables (للأمان في Vercel)
$host = getenv('DB_HOST') ?: 'db.dgbibiepsmbopwjbtoap.supabase.co';
$port = getenv('DB_PORT') ?: '5432';
$db   = getenv('DB_NAME') ?: 'postgres';
$user = getenv('DB_USER') ?: 'postgres';
$pass = getenv('DB_PASSWORD') ?: 'YOUR_PASSWORD_HERE'; // ضع كلمة المرور هنا مؤقتاً للاختبار المحلي
$charset = 'utf8';

// DSN لـ PostgreSQL
$dsn = "pgsql:host=$host;port=$port;dbname=$db;options='--client_encoding=$charset'";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    die('فشل الاتصال بقاعدة البيانات: ' . $e->getMessage());
}
?>
