<?php
require_once dirname(__DIR__, 2) . '/app/bootstrap.php'; 
require_company_admin();
$u = current_user();
?>
<!doctype html>
<html lang="tr">
<head>
  <meta charset="utf-8">
  <title>Sefer Oluştur</title>
  <link rel="stylesheet" href="/assets/css/style.css?v=6">
  <style>
    .form{display:grid;gap:.6rem;max-width:700px}
    .row{display:grid;grid-template-columns:1fr 1fr;gap:.6rem}
    @media(max-width:700px){.row{grid-template-columns:1fr}}
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
  <h1>Sefer Oluştur</h1>
  <div class="card" style="margin-top:.6rem">
    <form class="form" method="post" action="/company/trips_save.php">
      <div class="row">
        <div><label>Kalkış<input name="origin" required placeholder="İstanbul"></label></div>
        <div><label>Varış<input name="destination" required placeholder="Ankara"></label></div>
      </div>

      <div class="row">
        <div><label>Kalkış Zamanı<input type="datetime-local" name="departure" required></label></div>
        <div><label>Varış Zamanı<input type="datetime-local" name="arrival" required></label></div>
      </div>

      <div class="row">
        <div><label>Fiyat (TL)<input type="number" name="price_tl" min="0" step="0.01" required></label></div>
        <div><label>Toplam Koltuk<input type="number" name="total_seats" min="1" max="60" value="40" required></label></div>
      </div>

      <button class="btn" type="submit">Kaydet</button>
      <a class="link-btn" href="/company/index.php">Panele Dön</a>
    </form>
  </div>
</main>
</body>
</html>
