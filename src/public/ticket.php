<?php
require_once __DIR__ . '/../app/bootstrap.php';
require_login();

$u = current_user();
$ticket_id = (int)($_GET['id'] ?? 0);

$pdo = db();
$st = $pdo->prepare("
  SELECT t.*, tr.origin, tr.destination, tr.departure_time, tr.arrival_time, tr.price_cents, tr.firm_id,
         f.name AS firm_name
  FROM tickets t
  JOIN trips   tr ON tr.id = t.trip_id
  JOIN firms   f  ON f.id = tr.firm_id
  WHERE t.id = ?
");
$st->execute([$ticket_id]);
$T = $st->fetch();
if (!$T) { http_response_code(404); echo 'Bilet bulunamadı.'; exit; }

if ((int)$T['user_id'] !== (int)$u['id']) {
  set_flash('error', 'Bu bileti görüntüleme yetkin yok.');
  redirect('/account.php');
}

$dep = new DateTimeImmutable($T['departure_time']);
$arr = new DateTimeImmutable($T['arrival_time']);
?>
<!doctype html>
<html lang="tr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Bilet #<?= (int)$T['id'] ?></title>
<link rel="stylesheet" href="/assets/css/style.css?v=14">
<style>
  .ticket-wrap{max-width:800px;margin:1.2rem auto}
  .ticket{background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:1rem}
  .meta{display:grid;grid-template-columns:1fr 1fr;gap:1rem}
  @media(max-width:800px){.meta{grid-template-columns:1fr}}
  .muted{color:var(--muted)}
  .print-actions{display:flex;gap:.6rem;margin:.8rem 0}
 
  @media print {
    header, .print-actions, .site-footer { display:none !important; }
    body { background:#fff !important; }
    .ticket { border:none; box-shadow:none; }
  }

  .qr{
    width:120px;height:120px;border:2px solid #0b1020;border-radius:8px;
    display:flex;align-items:center;justify-content:center;font-weight:800;
  }
</style>
</head>
<body>
<header class="site-header">
  <nav class="nav">
    <a class="logo" href="/"><img class="logo-img" src="/assets/img/logo.png" alt=""><span class="logo-text">Jetlet Hızlı Bilet</span></a>
    <ul class="nav-list">
      <li><a href="/search.php">Sefer Ara</a></li>
      <li><a href="/account.php">Hesabım</a></li>
      <li><a href="/logout.php">Çıkış</a></li>
    </ul>
  </nav>
</header>

<main class="container ticket-wrap">
  <?= render_flash() ?>
  <div class="ticket">
    <div style="display:flex;justify-content:space-between;align-items:center;gap:1rem">
      <div>
        <h1 style="margin:0">Bilet #<?= (int)$T['id'] ?></h1>
        <div class="muted">Firma: <strong><?= htmlspecialchars($T['firm_name']) ?></strong></div>
      </div>
      <div class="qr"><?= (int)$T['id'] ?></div>
    </div>

    <hr style="margin:1rem 0;border-color:#e5e7eb">

    <div class="meta">
      <div>
        <p><strong>Rota:</strong> <?= htmlspecialchars($T['origin']) ?> → <?= htmlspecialchars($T['destination']) ?></p>
        <p><strong>Kalkış:</strong> <?= $dep->format('d.m.Y H:i') ?></p>
        <p><strong>Varış:</strong> <?= $arr->format('d.m.Y H:i') ?></p>
      </div>
      <div>
        <p><strong>Koltuk:</strong> <?= (int)$T['seat_no'] ?></p>
        <p><strong>Durum:</strong> <?= htmlspecialchars($T['status']) ?></p>
        <p><strong>Ödenen:</strong> <?= money_tl((int)$T['price_paid_cents']) ?></p>
        <?php if ((int)$T['discount_cents'] > 0): ?>
          <p class="muted">Kupon: <?= htmlspecialchars($T['coupon_code']) ?> (−<?= money_tl((int)$T['discount_cents']) ?>)</p>
        <?php endif; ?>
        <?php if (!empty($T['purchased_at'])): ?>
          <p class="muted">Satın alma: <?= (new DateTimeImmutable($T['purchased_at']))->format('d.m.Y H:i') ?></p>
        <?php endif; ?>
      </div>
    </div>

    <div class="print-actions">
      <button class="btn" onclick="window.print()">Yazdır</button>
      <a class="link-btn" href="/account.php">Hesabıma Dön</a>
    </div>
  </div>
</main>

<footer class="site-footer"><small>© 2025</small></footer>
</body>
</html>
