<?php
require_once dirname(__DIR__, 2) . '/app/bootstrap.php'; 
require_company_admin();
$u = current_user();
?>
<!doctype html>
<html lang="tr">
<head>
  <meta charset="utf-8">
  <title>Firma Paneli</title>
  <link rel="stylesheet" href="/assets/css/style.css?v=6">
  <style>
    .grid{display:grid;gap:.8rem;grid-template-columns:repeat(2,1fr)}
    @media(max-width:800px){.grid{grid-template-columns:1fr}}
  </style>
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
  <h1>Firma Paneli</h1>
  <p class="muted">Hoş geldiniz, <?=htmlspecialchars($u['name'])?>. Firmanız için sefer tanımlayabilirsiniz.</p>

  <div class="grid" style="margin-top:1rem">
    <div class="card">
      <h3>Yeni Sefer</h3>
      <p class="muted">Kalkış/varış, saatler ve fiyat ile yeni sefer ekleyin.</p>
      <p style="margin-top:.6rem"><a class="btn" href="/company/trips_new.php">Sefer Oluştur</a></p>
    </div>
    <div class="card">
  <h3>Kuponlar</h3>
  <p class="muted">Kupon kodu ekleyin, limit ve son tarih tanımlayın.</p>
  <p style="margin-top:.6rem"><a class="btn" href="/company/coupons_list.php">Kupon Listesi</a></p>
   </div>
    <div class="card">
      <h3>Seferlerim</h3>
      <p class="muted">Ekli seferleri görün ve düzenleme/iptal (yakında).</p>
      <p style="margin-top:.6rem"><a class="btn" href="/company/trips_list.php">Sefer Listesi</a></p>
    </div>
    <div class="card">
  <h3>Raporlar</h3>
  <p class="muted">Satış, gelir, kupon performansı.</p>
  <p style="margin-top:.6rem"><a class="btn" href="/company/reports.php">Raporları Aç</a></p>
</div>
  </div>
</main>
</body>
</html>
