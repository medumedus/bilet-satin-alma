<?php
require_once __DIR__ . '/../app/bootstrap.php';
$pdo = db();
$counts = [];
foreach (['users','firms','trips','coupons'] as $t) {
  $counts[$t] = (int)$pdo->query("SELECT COUNT(*) FROM $t")->fetchColumn();
}
header('Content-Type: application/json; charset=utf-8');
echo json_encode($counts, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE);
