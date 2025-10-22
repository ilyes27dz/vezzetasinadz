<?php
// ملف الاتصال بقاعدة البيانات PostgreSQL

// قراءة البيانات من Environment Variables
$host = getenv('DB_HOST') ?: 'aws-1-us-east-2.pooler.supabase.com';
$port = getenv('DB_PORT') ?: '6543';
$db   = getenv('DB_NAME') ?: 'postgres';
$user = getenv('DB_USER') ?: 'postgres.dgbibiepsmbopwjbtoap';
$pass = getenv('DB_PASSWORD') ?: '';

// DSN لـ PostgreSQL
$dsn = "pgsql:host=$host;port=$port;dbname=$db";

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
