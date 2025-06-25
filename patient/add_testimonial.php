<?php
session_start();
require_once '../db.php'; // التصحيح هنا
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'patient') {
    header("Location: ../auth/login.php"); exit;
}
if ($_SERVER['REQUEST_METHOD']=='POST' && isset($_POST['content'])) {
    $city = trim($_POST['city']);
    $content = trim($_POST['content']);
    $user_id = $_SESSION['user_id'];
    $stmt = $pdo->prepare("INSERT INTO testimonials (user_id, name, city, content, approved) VALUES (?, ?, ?, ?, 0)");
    $stmt->execute([$user_id, $_SESSION['name'], $city, $content]);
    header("Location: index.php?testimonial=sent");
    exit;
}
header("Location: index.php");