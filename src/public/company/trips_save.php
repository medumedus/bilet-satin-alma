<?php
require_once dirname(__DIR__, 2) . '/app/bootstrap.php';  // doğru yol
require_company_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  redirect('/company/trips_new.php');
}

$u = current_user();

$origin      = trim($_POST['origin'] ?? '');
$destination = trim($_POST['destination'] ?? '');
$departure   = trim($_POST['departure'] ?? '');
$arrival     = trim($_POST['arrival'] ?? '');
$price_tl    = (float)($_POST['price_tl'] ?? 0);
$total_seats = (int)($_POST['total_seats'] ?? 40);

if ($origin === '' || $destination === '' || $price_tl < 0 || $total_seats < 1) {
  set_flash('error', 'Lütfen tüm alanları doğru doldurun.');
  redirect('/company/trips_new.php');
}

try {
  $dep = (new DateTimeImmutable($departure))->format('Y-m-d H:i:s');
  $arr = (new DateTimeImmutable($arrival))->format('Y-m-d H:i:s');
} catch (Throwable $e) {
  set_flash('error', 'Tarih formatı hatalı.');
  redirect('/company/trips_new.php');
}

$price_cents = (int) round($price_tl * 100);

$pdo = db();
$st = $pdo->prepare(
  "INSERT INTO trips (firm_id, origin, destination, departure_time, arrival_time, price_cents, total_seats)
   VALUES (?, ?, ?, ?, ?, ?, ?)"
);
$st->execute([$u['firm_id'], $origin, $destination, $dep, $arr, $price_cents, $total_seats]);

set_flash('success', 'Sefer başarıyla oluşturuldu.');
redirect('/company/trips_list.php');
