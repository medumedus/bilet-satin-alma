<?php
require_once dirname(__DIR__, 2) . '/app/bootstrap.php';
require_company_admin();
$u = current_user();
?>
<!doctype html>
<html lang="tr"><head>
<meta charset="utf-8"><title>Yeni Kupon</title>
<link rel="stylesheet" href="/assets/css/style.css?v=12">
<style>.form{display:grid;gap:.6rem;max-width:720px}.row{display:grid;grid-template-columns:1fr 1fr;gap:.6rem}@media(max-width:720px){.row{grid-template-columns:1fr}}</style>
</head><body>
<header class="site-header">
  <nav class="nav">
    <a class="logo" href="/"><img src="/assets/img/logo.png" class="logo-img"><span class="logo-text">Bilet Platformu</span></a>
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
  <h1>Yeni Kupon</h1>
  <div class="card" style="margin-top:.6rem">
    <form class="form" method="post" action="/company/coupons_save.php">
      <div class="row">
        <div><label>Kod <input name="code" required placeholder="YAZ20"></label></div>
        <div>
          <label>Tür
            <select name="type" required>
              <option value="percent">Yüzde</option>
              <option value="fixed">Sabit (TL)</option>
            </select>
          </label>
        </div>
      </div>

      <div class="row">
        <div><label>Değer <input name="value" type="number" step="1" min="1" required placeholder="Yüzde için 5..90, Sabit için TL"></label></div>
        <div><label>Min Fiyat (TL) <input name="min_price_tl" type="number" step="0.01" min="0" value="0"></label></div>
      </div>

      <div class="row">
        <div><label>Maks. Kullanım (0 = sınırsız) <input name="max_uses" type="number" step="1" min="0" value="0"></label></div>
        <div><label>Son Tarih <input name="expires" type="datetime-local"></label></div>
      </div>

      <div><label><input type="checkbox" name="is_active" checked> Aktif</label></div>

      <button class="btn" type="submit">Kaydet</button>
      <a class="link-btn" href="/company/coupons_list.php">Listeye Dön</a>
    </form>
  </div>
</main>
</body></html>
