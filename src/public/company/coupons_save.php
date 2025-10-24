<?php
require_once dirname(__DIR__, 2) . '/app/bootstrap.php';
require_company_admin();
$u = current_user();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { redirect('/company/coupons_new.php'); }

$firm_id   = (int)($u['firm_id'] ?? 0);
$code      = strtoupper(trim($_POST['code'] ?? ''));
$type      = (($_POST['type'] ?? 'percent') === 'fixed') ? 'fixed' : 'percent';
$value_in  = (float)($_POST['value'] ?? 0);
$min_tl    = (float)($_POST['min_price_tl'] ?? 0);
$max_uses  = (int)($_POST['max_uses'] ?? 0);
$expires   = trim($_POST['expires'] ?? '');
$is_active = isset($_POST['is_active']) ? 1 : 0;

if ($firm_id <= 0) { set_flash('error','Firma ID bulunamadı (oturum).'); redirect('/company/coupons_new.php'); }
if ($code === '' || $value_in <= 0) { set_flash('error','Kod ve değer zorunlu.'); redirect('/company/coupons_new.php'); }

$value              = ($type === 'percent') ? (int)round($value_in) : (int)round($value_in * 100); 
$min_price_cents    = (int)round($min_tl * 100);
$expires_at         = $expires ? (new DateTimeImmutable($expires))->format('Y-m-d H:i:s') : null;

$pdo  = db();
$cols = $pdo->query("PRAGMA table_info(coupons)")->fetchAll();
$colnames = array_map(fn($r)=> $r['name'], $cols);

$has_discount_percent = in_array('discount_percent', $colnames, true);
$has_usage_limit      = in_array('usage_limit', $colnames, true);

$discount_percent = ($type === 'percent') ? $value : 0;
$usage_limit = $max_uses;

$fields = ['firm_id','code','type','value','max_uses','used_count','min_price_cents','expires_at','is_active'];
$params = [$firm_id,$code,$type,$value,$max_uses,0,$min_price_cents,$expires_at,$is_active];

if ($has_discount_percent) { $fields[]='discount_percent'; $params[]=$discount_percent; }
if ($has_usage_limit)      { $fields[]='usage_limit';      $params[]=$usage_limit;      }

$placeholders = implode(',', array_fill(0, count($fields), '?'));
$fieldlist    = implode(',', $fields);

try {
  $st = $pdo->prepare("INSERT INTO coupons ($fieldlist) VALUES ($placeholders)");
  $st->execute($params);

  set_flash('success', 'Kupon kaydedildi: '.$code.' | firma #'.$firm_id);
  redirect('/company/coupons_list.php');
} catch (Throwable $e) {
  set_flash('error', 'Kupon kaydedilemedi: '.$e->getMessage());
  redirect('/company/coupons_new.php');
}
