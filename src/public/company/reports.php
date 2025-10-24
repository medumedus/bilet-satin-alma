<?php
require_once dirname(__DIR__, 2) . '/app/bootstrap.php';
require_company_admin();
$u   = current_user();
$pdo = db();
$df = $_GET['from'] ?? (new DateTimeImmutable('-30 days'))->format('Y-m-d');
$dt = $_GET['to']   ?? (new DateTimeImmutable('now'))->format('Y-m-d');
$from = (new DateTimeImmutable($df.' 00:00:00'))->format('Y-m-d H:i:s');
$to   = (new DateTimeImmutable($dt.' 23:59:59'))->format('Y-m-d H:i:s');

$sum = $pdo->prepare("
  SELECT COUNT(*) AS ticket_count,
         COALESCE(SUM(price_paid_cents),0) AS revenue_cents,
         COALESCE(AVG(price_paid_cents),0) AS avg_cents,
         COUNT(DISTINCT user_id) AS uniq_users
  FROM tickets t
  JOIN trips   tr ON tr.id = t.trip_id
  WHERE tr.firm_id = ? AND t.status='purchased' AND t.purchased_at BETWEEN ? AND ?
");
$sum->execute([(int)$u['firm_id'], $from, $to]);
$summary = $sum->fetch();

$daily = $pdo->prepare("
  SELECT DATE(t.purchased_at) AS day,
         SUM(t.price_paid_cents) AS revenue_cents,
         COUNT(*) AS tickets
  FROM tickets t
  JOIN trips tr ON tr.id = t.trip_id
  WHERE tr.firm_id = ? AND t.status='purchased' AND t.purchased_at BETWEEN ? AND ?
  GROUP BY DATE(t.purchased_at)
  ORDER BY day
");
$daily->execute([(int)$u['firm_id'], $from, $to]);
$daily_rows = $daily->fetchAll();

$routes = $pdo->prepare("
  SELECT tr.origin, tr.destination,
         COUNT(*) AS tickets,
         SUM(t.price_paid_cents) AS revenue_cents
  FROM tickets t
  JOIN trips tr ON tr.id = t.trip_id
  WHERE tr.firm_id=? AND t.status='purchased' AND t.purchased_at BETWEEN ? AND ?
  GROUP BY tr.origin, tr.destination
  ORDER BY revenue_cents DESC
");
$routes->execute([(int)$u['firm_id'], $from, $to]);
$route_rows = $routes->fetchAll();

$coupons = $pdo->prepare("
  SELECT COALESCE(t.coupon_code,'(yok)') AS code,
         COUNT(*) AS used_times,
         SUM(t.discount_cents) AS total_discount
  FROM tickets t
  JOIN trips tr ON tr.id = t.trip_id
  WHERE tr.firm_id=? AND t.status='purchased' AND t.purchased_at BETWEEN ? AND ?
  GROUP BY t.coupon_code
  ORDER BY total_discount DESC
");
$coupons->execute([(int)$u['firm_id'], $from, $to]);
$coupon_rows = $coupons->fetchAll();
?>
<!doctype html>
<html lang="tr"><head>
<meta charset="utf-8"><title>Raporlar</title>
<link rel="stylesheet" href="/assets/css/style.css?v=13">
<style>
  .grid{display:grid;gap:.8rem;grid-template-columns:1fr 1fr}
  @media(max-width:900px){.grid{grid-template-columns:1fr}}
  table td,table th{text-align:left}
</style>
</head><body>
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
  <h1>Raporlar</h1>

  <!-- Tarih filtresi -->
  <form class="card" method="get" style="display:grid;gap:.6rem;max-width:620px">
    <div class="grid">
      <label>Başlangıç<input type="date" name="from" value="<?=htmlspecialchars($df)?>"></label>
      <label>Bitiş<input type="date" name="to"   value="<?=htmlspecialchars($dt)?>"></label>
    </div>
    <div>
      <button class="btn" type="submit">Uygula</button>
      <a class="link-btn" href="/company/reports_csv.php?from=<?=$df?>&to=<?=$dt?>&type=daily">Günlük Gelir CSV</a>
      <a class="link-btn" href="/company/reports_csv.php?from=<?=$df?>&to=<?=$dt?>&type=routes">Rotalar CSV</a>
      <a class="link-btn" href="/company/reports_csv.php?from=<?=$df?>&to=<?=$dt?>&type=coupons">Kuponlar CSV</a>
    </div>
  </form>

  <!-- Özet kutuları -->
  <div class="grid">
    <div class="card">
      <h3>Dönem Özeti</h3>
      <p>Bilet: <strong><?= (int)$summary['ticket_count'] ?></strong></p>
      <p>Gelir: <strong><?= money_tl((int)$summary['revenue_cents']) ?></strong></p>
      <p>Ortalama Fiyat: <strong><?= money_tl((int)$summary['avg_cents']) ?></strong></p>
      <p>Tekil Müşteri: <strong><?= (int)$summary['uniq_users'] ?></strong></p>
    </div>

    <div class="card">
      <h3>Günlük Gelir</h3>
      <table class="table">
        <thead><tr><th>Tarih</th><th>Gelir</th><th>Bilet</th></tr></thead>
        <tbody>
          <?php foreach($daily_rows as $r): ?>
            <tr>
              <td><?= htmlspecialchars($r['day']) ?></td>
              <td><?= money_tl((int)$r['revenue_cents']) ?></td>
              <td><?= (int)$r['tickets'] ?></td>
            </tr>
          <?php endforeach; ?>
          <?php if(!$daily_rows): ?><tr><td colspan="3" class="muted">Kayıt yok.</td></tr><?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Rota performansı -->
  <div class="card" style="margin-top:.8rem">
    <h3>Rota Bazlı Satış</h3>
    <table class="table">
      <thead><tr><th>Rota</th><th>Bilet</th><th>Gelir</th></tr></thead>
      <tbody>
        <?php foreach($route_rows as $r): ?>
          <tr>
            <td><?= htmlspecialchars($r['origin'].' → '.$r['destination']) ?></td>
            <td><?= (int)$r['tickets'] ?></td>
            <td><?= money_tl((int)$r['revenue_cents']) ?></td>
          </tr>
        <?php endforeach; ?>
        <?php if(!$route_rows): ?><tr><td colspan="3" class="muted">Kayıt yok.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>

  <!-- Kupon performansı -->
  <div class="card" style="margin-top:.8rem">
    <h3>Kupon Kullanımı</h3>
    <table class="table">
      <thead><tr><th>Kupon</th><th>Kullanım</th><th>Toplam İndirim</th></tr></thead>
      <tbody>
        <?php foreach($coupon_rows as $r): ?>
          <tr>
            <td><?= htmlspecialchars($r['code']) ?></td>
            <td><?= (int)$r['used_times'] ?></td>
            <td><?= money_tl((int)$r['total_discount']) ?></td>
          </tr>
        <?php endforeach; ?>
        <?php if(!$coupon_rows): ?><tr><td colspan="3" class="muted">Kayıt yok.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
</main>
</body></html>
