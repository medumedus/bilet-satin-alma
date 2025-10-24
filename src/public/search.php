<?php
require_once __DIR__ . '/../app/bootstrap.php';

$pdo = db();

$origin = trim($_GET['origin'] ?? '');
$destination = trim($_GET['destination'] ?? '');
$date = trim($_GET['date'] ?? ''); 

$where = [];
$params = [];

if ($origin !== '') {
  $where[] = "LOWER(origin) LIKE LOWER(?)";
  $params[] = "%$origin%";
}
if ($destination !== '') {
  $where[] = "LOWER(destination) LIKE LOWER(?)";
  $params[] = "%$destination%";
}
if ($date !== '') {
  $start = $date . ' 00:00:00';
  $end   = $date . ' 23:59:59';
  $where[] = "departure_time BETWEEN ? AND ?";
  $params[] = $start;
  $params[] = $end;
} else {
  $where[] = "departure_time >= datetime('now')";
}

$sql = "SELECT trips.*, firms.name AS firm_name
        FROM trips
        JOIN firms ON firms.id = trips.firm_id";

if ($where) {
  $sql .= " WHERE " . implode(" AND ", $where);
}
$sql .= " ORDER BY departure_time ASC
          LIMIT 200"; 

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$trips = $stmt->fetchAll();

function money_tl(int $cents): string {
  return number_format($cents/100, 2, ',', '.') . ' TL';
}

$u = current_user();
?>
<!doctype html>
<html lang="tr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Sefer Ara</title>
  <link rel="stylesheet" href="/assets/css/style.css?v=4">
  <style>
    .search-form{display:grid;gap:.6rem;margin:1rem 0;grid-template-columns:1fr 1fr 1fr auto}
    .search-form input{padding:.55rem}
    .trip-list{display:grid;gap:.6rem}
    .trip-card{background:#111827;padding:1rem;border-radius:.6rem;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:.8rem}
    .trip-meta{opacity:.9}
    .chip{display:inline-block;background:#0b1020;border:1px solid #223; padding:.2rem .5rem;border-radius:.4rem;font-size:.85rem;opacity:.9}
    .right{display:flex;gap:.5rem;align-items:center}
    .link-btn{background:#60a5fa;color:#0b1020;text-decoration:none;padding:.45rem .7rem;border-radius:.5rem;font-weight:700}
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
  <li><a href="/search.php">Sefer Ara</a></li>

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
  <h1>Sefer Ara</h1>
  <form class="search-form" method="get" action="/search.php">
    <input name="origin" placeholder="Kalkış (örn. İstanbul)" value="<?=htmlspecialchars($origin)?>">
    <input name="destination" placeholder="Varış (örn. Ankara)" value="<?=htmlspecialchars($destination)?>">
    <input type="date" name="date" value="<?=htmlspecialchars($date)?>" min="<?= (new DateTime('today'))->format('Y-m-d') ?>">

    <button class="btn" type="submit">Ara</button>
  </form>

 
  <?php if (!$trips): ?>
    <p>Uygun sefer bulunamadı.</p>
  <?php else: ?>
    <div class="trip-list">
      <?php foreach ($trips as $t): ?>
        <?php
          $dep = (new DateTimeImmutable($t['departure_time']))->format('d.m.Y H:i');
          $arr = (new DateTimeImmutable($t['arrival_time']))->format('d.m.Y H:i');
        ?>
        <div class="trip-card">
          <div>
            <strong><?=htmlspecialchars($t['firm_name'])?></strong>
            <div class="trip-meta">
              <?=htmlspecialchars($t['origin'])?> → <?=htmlspecialchars($t['destination'])?>
              <span class="chip"><?=$dep?> kalkış</span>
              <span class="chip"><?=$arr?> varış</span>
              <span class="chip"><?= (int)$t['total_seats'] ?> koltuk</span>
            </div>
          </div>
          <div class="right">
            <strong><?=money_tl((int)$t['price_cents'])?></strong>
        
            <a class="link-btn" href="/trip.php?id=<?=$t['id']?>">Detay</a>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</main>

<footer class="site-footer">
  <small>© 2025</small>
</footer>
</body>
</html>
