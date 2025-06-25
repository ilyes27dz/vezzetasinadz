<?php
require_once '../db.php';
$pharmacy_id = intval($_GET['pharmacy_id']);
$stmt = $pdo->prepare("SELECT name, price FROM pharmacy_drugs WHERE pharmacy_id=? ORDER BY name");
$stmt->execute([$pharmacy_id]);
$drugs = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
echo json_encode($drugs, JSON_UNESCAPED_UNICODE);
?>