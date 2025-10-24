<?php
require_once __DIR__ . '/../app/bootstrap.php';
?>
<!doctype html>
<html lang="tr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Ana Sayfa | Jetlet</title>
  <link rel="stylesheet" href="/assets/css/style.css?v=4">
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
  <section class="hero">
    <h1>Jet Hızında Bilet Satın Alma Platformu</h1>
    <p class="muted">Kalkış/Varış bilgisi ile sefer arayın, giriş yaptıktan sonra bilet satın alın.</p>
  </section>
  <section class="card" style="margin-top:1rem">
    <form class="quick-search" method="get" action="/search.php">
      <input name="origin" placeholder="Kalkış (örn. İstanbul)" required>
      <input name="destination" placeholder="Varış (örn. Ankara)" required>
      <input type="date" name="date" min="<?= (new DateTime('today'))->format('Y-m-d') ?>">
      <button class="btn" type="submit">Sefer Ara</button>
    </form>
  </section>
  <section style="margin-top:1rem">
    <h2 class="section-title">Popüler Rotalar</h2>
    <div class="chip-list">
      <a class="chip-link" href="/search.php?origin=İstanbul&destination=Ankara">İstanbul → Ankara</a>
      <a class="chip-link" href="/search.php?origin=İzmir&destination=Bursa">İzmir → Bursa</a>
      <a class="chip-link" href="/search.php?origin=Antalya&destination=İstanbul">Antalya → İstanbul</a>
      <a class="chip-link" href="/search.php?origin=Ankara&destination=Eskişehir">Ankara → Eskişehir</a>
    </div>
  </section>
  <section style="margin-top:1rem">
    <h2 class="section-title">Neden Biz?</h2>
    <div class="feature-grid">
      <div class="card feature">
        <div class="feature-emoji">💸</div>
        <h3>Uygun Fiyat</h3>
        <p class="muted">Kupon desteği ve net fiyatlar — sürpriz yok.</p>
      </div>
      <div class="card feature">
        <div class="feature-emoji">⏪</div>
        <h3>Kolay İptal</h3>
        <p class="muted">Kalkışa ≥ 1 saat kala iptal edip kredini geri al.</p>
      </div>
      <div class="card feature">
        <div class="feature-emoji">🔐</div>
        <h3>Güvenli İşlem</h3>
        <p class="muted">Bakiye ile tek tık ödeme, kayıtlı biletler “Hesabım”da.</p>
      </div>
    </div>
  </section>

  <footer class="site-footer"><small>© 2025</small></footer>
</main>
  <footer class="site-footer">
    <small>© 2025</small>
  </footer>
</body>
</html>
