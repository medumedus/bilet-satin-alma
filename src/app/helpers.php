<?php
function redirect(string $path){
  header("Location: $path");
  exit;
}
function now(): DateTimeImmutable { 
  return new DateTimeImmutable('now'); 
}
function set_flash(string $key, string $msg){
  $_SESSION['_flash'][$key] = $msg;
}
function flash(string $key): ?string {
  if (!empty($_SESSION['_flash'][$key])) {
    $m = $_SESSION['_flash'][$key];
    unset($_SESSION['_flash'][$key]);
    return $m;
  }
  return null;
}
function status_tr(string $status): string {
  return match ($status) {
    'purchased' => 'Satın alındı',
    'cancelled' => 'İptal edildi',
    default => ucfirst($status),
  };
}
function render_flash(): string {
  $err = flash('error'); $ok = flash('success');
  $html = '';
  if ($err) $html .= '<p style="color:#fca5a5">'.htmlspecialchars($err).'</p>';
  if ($ok)  $html .= '<p style="color:#86efac">'.htmlspecialchars($ok).'</p>';
  return $html;
}
if (!function_exists('money_tl')) {
  function money_tl(int $cents): string {
    return number_format($cents/100, 2, ',', '.') . ' TL';
  }
}
function nav_active(string $path): string {
  $uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
  return str_starts_with($uri, $path) ? 'active' : '';
}
