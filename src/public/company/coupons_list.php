<?php
require_once dirname(__DIR__, 2) . '/app/bootstrap.php';
require_company_admin();
$u = current_user();
$pdo = db();

$st = $pdo->prepare("
  SELECT * FROM coupons
  WHERE firm_id = ? OR firm_id IS NULL
  ORDER BY id DESC
");
$st->execute([(int)$u['firm_id']]);
$rows = $st->fetchAll();


function dt($s){ return $s ? (new DateTimeImmutable($s))->format('d.m.Y H:i') : '-'; }
?>
<!doctype html>
<html lang="tr"><head>
<meta charset="utf-8"><title>Kuponlar</title>
<link rel="stylesheet" href="/assets/css/style.css?v=9">
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
  <h1>Kuponlar</h1>
  <p style="margin:.6rem 0"><a class="btn" href="/company/coupons_new.php">Yeni Kupon</a></p>

  <?php if (!$rows): ?>
    <p class="muted">Henüz kupon yok.</p>
  <?php else: ?>
    <div class="card">
      <table class="table">
        <thead><tr>
          <th>Kod</th><th>Tür</th><th>Değer</th><th>Min Fiyat</th><th>Kullanım</th><th>Son Tarih</th><th>Durum</th>
        </tr></thead>
        <tbody>
        <?php foreach($rows as $r): ?>
          <tr>
            <td><?= htmlspecialchars($r['code']) ?></td>
            <td><?= $r['type']==='percent'?'Yüzde':'Sabit' ?></td>
            <td><?= $r['type']==='percent' ? (int)$r['value'].'%' : money_tl((int)$r['value']) ?></td>
            <td><?= money_tl((int)$r['min_price_cents']) ?></td>
            <td><?= (int)$r['used_count'] ?><?= $r['max_uses'] ? ' / '.(int)$r['max_uses'] : ' (sınırsız)' ?></td>
            <td><?= dt($r['expires_at']) ?></td>
            <td><?= ((int)$r['is_active']) ? 'Aktif' : 'Pasif' ?></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</main>
</body></html>
