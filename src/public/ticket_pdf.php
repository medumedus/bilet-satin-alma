<?php
require_once __DIR__ . '/../app/bootstrap.php';
require_login();

$u = current_user();
$ticket_id = (int)($_GET['id'] ?? 0);
$pdo = db();

$st = $pdo->prepare("
  SELECT t.*, tr.origin, tr.destination, tr.departure_time, tr.arrival_time, tr.price_cents, f.name AS firm_name
  FROM tickets t
  JOIN trips tr ON tr.id = t.trip_id
  JOIN firms f ON f.id = tr.firm_id
  WHERE t.id = ?
");
$st->execute([$ticket_id]);
$t = $st->fetch();

if (!$t) { http_response_code(404); echo "Bilet bulunamadı."; exit; }
if ((int)$t['user_id'] !== (int)$u['id']) { http_response_code(403); echo "Yetkiniz yok."; exit; }
function money_tl(int $c){ return number_format($c/100, 2, ',', '.') . ' TL'; }
$dep = (new DateTimeImmutable($t['departure_time']))->format('d.m.Y H:i');
$arr = (new DateTimeImmutable($t['arrival_time']))->format('d.m.Y H:i');

$html = '
<!doctype html>
<html lang="tr">
<head>
<meta charset="utf-8">
<style>
  body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color:#111; }
  .wrap { width: 100%; padding: 16px; }
  .head { display:flex; justify-content:space-between; align-items:center; }
  .title { font-size: 18px; font-weight: bold; }
  .box { border:1px solid #999; border-radius:6px; padding:12px; margin-top:10px; }
  .row { display:flex; justify-content:space-between; }
  .muted { color:#555; }
  .badge { display:inline-block; border:1px solid #999; border-radius:4px; padding:2px 6px; font-size:11px; }
</style>
</head>
<body>
<div class="wrap">
  <div class="head">
    <div class="title">Bilet #' . (int)$t['id'] . '</div>
    <div class="badge">'.htmlspecialchars($t['firm_name']).'</div>
  </div>

  <div class="box">
    <div class="row"><div><strong>Yolcu:</strong> '.htmlspecialchars($u['name']).'</div><div><strong>E-posta:</strong> '.htmlspecialchars($u['email']).'</div></div>
    <div class="row" style="margin-top:6px"><div><strong>Rota:</strong> '.htmlspecialchars($t['origin']).' → '.htmlspecialchars($t['destination']).'</div><div><strong>Koltuk:</strong> '.(int)$t['seat_no'].'</div></div>
    <div class="row" style="margin-top:6px"><div><strong>Kalkış:</strong> '.$dep.'</div><div><strong>Varış:</strong> '.$arr.'</div></div>
    <div class="row" style="margin-top:6px"><div><strong>Ödenen:</strong> '.money_tl((int)$t['price_paid_cents']).'</div><div class="muted"><strong>Durum:</strong> '.($t['status']==='purchased'?'Satın alındı':'İptal edildi').'</div></div>
  </div>

  <p class="muted" style="margin-top:10px">Bu belge elektronik ortamda üretilmiştir. Kontrol amaçlı bilet no yeterlidir.</p>
</div>
</body>
</html>
';

require_once __DIR__ . '/../vendor/autoload.php';
use Dompdf\Dompdf;

$dompdf = new Dompdf([
  'isRemoteEnabled' => false,
  'isHtml5ParserEnabled' => true,
  'chroot' => __DIR__ . '/../',
]);
$dompdf->loadHtml($html, 'UTF-8');
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();
$filename = 'bilet-' . (int)$t['id'] . '.pdf';
$dompdf->stream($filename, ['Attachment' => true]);
