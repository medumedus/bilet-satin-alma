<?php
require_once __DIR__ . '/../app/bootstrap.php';

$pdo = db();
$id = (int)($_GET['id'] ?? 0);

$stmt = $pdo->prepare("
  SELECT trips.*, firms.name AS firm_name
  FROM trips
  JOIN firms ON firms.id = trips.firm_id
  WHERE trips.id = ?
");
$stmt->execute([$id]);
$trip = $stmt->fetch();
if (!$trip) { http_response_code(404); echo "<h1>Sefer bulunamadı</h1>"; exit; }

$depDT = new DateTimeImmutable($trip['departure_time']);
$arrDT = new DateTimeImmutable($trip['arrival_time']);

$st = $pdo->prepare("SELECT seat_no FROM tickets WHERE trip_id = ? AND status = 'purchased'");
$st->execute([$trip['id']]);
$sold = array_map(fn($r)=> (int)$r['seat_no'], $st->fetchAll());

$total = (int)$trip['total_seats'];
$available = [];
for ($i=1; $i <= $total; $i++) {
  if (!in_array($i, $sold, true)) $available[] = $i;
}

$u = current_user();
?>
<!doctype html>
<html lang="tr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Sefer Detayı</title>
  <link rel="stylesheet" href="/assets/css/style.css?v=10">
  <style>
    .row{display:grid;grid-template-columns:1fr 1fr;gap:1rem}
    @media(max-width:768px){.row{grid-template-columns:1fr}}
    .muted{color:var(--muted)}
    .buy-form{margin-top:1rem;display:grid;gap:.6rem;max-width:460px}
  </style>
</head>
<body>
<header class="site-header">
  <nav class="nav">
    <a class="logo" href="/">
      <img src="/assets/img/logo.png" alt="Bilet Platformu" class="logo-img">
      <span class="logo-text">Jetlet Hızlı Bilet</span>
    </a>
    <ul class="nav-list">
      <li><a class="active" href="/search.php">Sefer Ara</a></li>
      <?php if (current_user()): ?>
        <?php if (is_company_admin()): ?>
          <li><a href="/company/index.php">Firma Paneli</a></li>
        <?php endif; ?>
        <li><a href="/account.php">Hesabım</a></li>
        <li><a href="/logout.php">Çıkış</a></li>
      <?php else: ?>
        <li><a href="/login.php">Giriş</a></li>
        <li><a href="/register.php">Kayıt</a></li>
      <?php endif; ?>
    </ul>
  </nav>
</header>

<main class="container">
  <?= render_flash() ?>
  <h1>Sefer Detayı</h1>

  <div class="card">
    <div class="row">
      <div>
        <p><strong>Firma:</strong> <?=htmlspecialchars($trip['firm_name'])?></p>
        <p><strong>Rota:</strong> <?=htmlspecialchars($trip['origin'])?> → <?=htmlspecialchars($trip['destination'])?></p>
        <p><strong>Kalkış:</strong> <?=$depDT->format('d.m.Y H:i')?></p>
        <p><strong>Varış:</strong> <?=$arrDT->format('d.m.Y H:i')?></p>
      </div>
      <div>
        <p><strong>Fiyat:</strong> <?=money_tl((int)$trip['price_cents'])?></p>
        <p><strong>Toplam Koltuk:</strong> <?=$total?></p>
        <p><strong>Boş Koltuk:</strong> <?=count($available)?></p>
        <?php if (count($available) === 0): ?>
          <p class="muted">Bu seferde boş koltuk kalmadı.</p>
        <?php endif; ?>
      </div>
    </div>

    <?php if (!$u): ?>
      <p style="margin-top:1rem">Satın almak için <a class="btn" href="/login.php">giriş yap</a> ya da <a class="btn" href="/register.php">kayıt ol</a>.</p>

    <?php else: ?>
      <?php if (($u['role'] ?? 'user') !== 'user'): ?>
        <p class="muted" style="margin-top:1rem">Not: Satın alma yalnızca <strong>User</strong> rolü için aktiftir.</p>

      <?php elseif (count($available) > 0 && $depDT > new DateTimeImmutable('now')): ?>
        <form method="post" action="/buy.php" class="buy-form">
          <input type="hidden" name="trip_id" value="<?=$trip['id']?>">

          <label>Koltuk Seç
            <select name="seat_no" required>
              <?php foreach ($available as $s): ?>
                <option value="<?=$s?>">Koltuk <?=$s?></option>
              <?php endforeach; ?>
            </select>
          </label>
          <label>Kupon Kodu (opsiyonel)
            <input name="coupon" placeholder="Örn: YAZ20">
          </label>

          <button class="btn" type="submit">Satın Al</button>
          <p class="muted">Ödeme: mevcut <em>kredi bakiyenden</em> düşülecek.</p>
        </form>

      <?php else: ?>
        <p class="muted" style="margin-top:1rem">Bu sefer için satın alma kapalı (koltuk yok veya kalkış zamanı geçti).</p>
      <?php endif; ?>
    <?php endif; ?>
  </div>
</main>

<footer class="site-footer">
  <small>© 2025</small>
</footer>
</body>
</html>
