<?php
require_once dirname(__DIR__, 2) . '/app/bootstrap.php'; 
require_company_admin();
$u = current_user();

$pdo = db();
$st = $pdo->prepare("SELECT * FROM trips WHERE firm_id = ? ORDER BY departure_time DESC");
$st->execute([$u['firm_id']]);
$trips = $st->fetchAll();

function dt($s){ return (new DateTimeImmutable($s))->format('d.m.Y H:i'); }
?>
<!doctype html>
<html lang="tr">
<head>
  <meta charset="utf-8">
  <title>Sefer Listesi</title>
  <link rel="stylesheet" href="/assets/css/style.css?v=6">
</head>
<body>
<header class="site-header">
  <nav class="nav">
    <a class="logo" href="/"><img src="/assets/img/logo.png" class="logo-img"><span class="logo-text">Jetlet Hızlı Bilet</span></a>
    <ul class="nav-list">
      <li><a href="/search.php">Sefer Ara</a></li>
      <li><a class="active" href="/company/index.php">Firma Paneli</a></li>
      <li><a href="/account.php">Hesabım</a></li>
      <li><a href="/logout.php">Çıkış</a></li>
    </ul>
  </nav>
</header>

<main class="container">
  <?= render_flash() ?>
  <h1>Seferlerim</h1>
  <p style="margin:.6rem 0"><a class="btn" href="/company/trips_new.php">Yeni Sefer</a></p>

  <?php if (!$trips): ?>
    <p class="muted">Henüz sefer yok.</p>
  <?php else: ?>
    <div class="card">
      <table class="table">
        <thead><tr>
          <th>ID</th><th>Rota</th><th>Kalkış</th><th>Varış</th><th>Fiyat</th><th>Koltuk</th>
        </tr></thead>
        <tbody>
        <?php foreach($trips as $t): ?>
          <tr>
            <td><?= (int)$t['id'] ?></td>
            <td><?= htmlspecialchars($t['origin']) ?> → <?= htmlspecialchars($t['destination']) ?></td>
            <td><?= dt($t['departure_time']) ?></td>
            <td><?= dt($t['arrival_time']) ?></td>
            <td><?= money_tl((int)$t['price_cents']) ?></td>
            <td><?= (int)$t['total_seats'] ?></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</main>
</body>
</html>
