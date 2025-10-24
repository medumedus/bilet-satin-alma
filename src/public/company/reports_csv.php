<?php
require_once dirname(__DIR__, 2) . '/app/bootstrap.php';
require_company_admin();
$u   = current_user();
$pdo = db();

$df = $_GET['from'] ?? (new DateTimeImmutable('-30 days'))->format('Y-m-d');
$dt = $_GET['to']   ?? (new DateTimeImmutable('now'))->format('Y-m-d');
$from = (new DateTimeImmutable($df.' 00:00:00'))->format('Y-m-d H:i:s');
$to   = (new DateTimeImmutable($dt.' 23:59:59'))->format('Y-m-d H:i:s');

$type = $_GET['type'] ?? 'daily';
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=report_'.$type.'_'.$df.'_'.$dt.'.csv');

$out = fopen('php://output','w');

if ($type === 'routes') {
  fputcsv($out, ['origin','destination','tickets','revenue_tl']);
  $st = $pdo->prepare("
    SELECT tr.origin, tr.destination, COUNT(*) tickets, SUM(t.price_paid_cents) revenue_cents
    FROM tickets t JOIN trips tr ON tr.id=t.trip_id
    WHERE tr.firm_id=? AND t.status='purchased' AND t.purchased_at BETWEEN ? AND ?
    GROUP BY tr.origin, tr.destination ORDER BY revenue_cents DESC
  ");
  $st->execute([(int)$u['firm_id'], $from, $to]);
  foreach ($st->fetchAll() as $r) fputcsv($out, [$r['origin'],$r['destination'],$r['tickets'], number_format($r['revenue_cents']/100,2,'.','')]);

} elseif ($type === 'coupons') {
  fputcsv($out, ['coupon_code','used_times','total_discount_tl']);
  $st = $pdo->prepare("
    SELECT COALESCE(t.coupon_code,'(yok)') code, COUNT(*) used_times, SUM(t.discount_cents) total_discount
    FROM tickets t JOIN trips tr ON tr.id=t.trip_id
    WHERE tr.firm_id=? AND t.status='purchased' AND t.purchased_at BETWEEN ? AND ?
    GROUP BY t.coupon_code ORDER BY total_discount DESC
  ");
  $st->execute([(int)$u['firm_id'], $from, $to]);
  foreach ($st->fetchAll() as $r) fputcsv($out, [$r['code'],$r['used_times'], number_format($r['total_discount']/100,2,'.','')]);

} else { 
  fputcsv($out, ['day','tickets','revenue_tl']);
  $st = $pdo->prepare("
    SELECT DATE(t.purchased_at) day, COUNT(*) tickets, SUM(t.price_paid_cents) revenue_cents
    FROM tickets t JOIN trips tr ON tr.id=t.trip_id
    WHERE tr.firm_id=? AND t.status='purchased' AND t.purchased_at BETWEEN ? AND ?
    GROUP BY DATE(t.purchased_at) ORDER BY day
  ");
  $st->execute([(int)$u['firm_id'], $from, $to]);
  foreach ($st->fetchAll() as $r) fputcsv($out, [$r['day'],$r['tickets'], number_format($r['revenue_cents']/100,2,'.','')]);
}

fclose($out);
