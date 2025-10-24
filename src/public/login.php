<?php
require_once __DIR__ . '/../app/bootstrap.php'; 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $email = trim($_POST['email'] ?? '');
  $pass  = $_POST['password'] ?? '';
  $u = authenticate($email, $pass);

  if (!$u) {
    set_flash('error', 'E-posta veya parola hatalı.');
    redirect('/login.php');
  }

  login_user($u);
  set_flash('success', 'Giriş başarılı.');
  redirect('/account.php');
}
?>
<!doctype html>
<html lang="tr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Giriş Yap</title>
  <link rel="stylesheet" href="/assets/css/style.css?v=10002">
  <style>
    .auth-wrap{max-width:640px;margin:2.5rem auto}
    .auth-card{padding:1.2rem;border:1px solid #e5e7eb;border-radius:12px;background:#fff}
    .auth-form{display:grid;gap:.8rem;margin-top:.6rem}
    .auth-form label{font-weight:600}
    .auth-form .btn{
      display:block !important;width:100% !important;
      background:#60a5fa !important;color:#0b1020 !important;
      border:2px solid #0b1020 !important;padding:14px 16px !important;
      border-radius:10px !important;font-weight:800 !important;text-align:center !important;
    }
  </style>
</head>
<body>
<header class="site-header">
  <nav class="nav">
    <a class="logo" href="/">
      <img src="/assets/img/logo.png" class="logo-img" alt="">
      <span class="logo-text">Jetlet Hızlı Bilet</span>
    </a>
    <ul class="nav-list">
      <li><a href="/search.php">Sefer Ara</a></li>
      <li><a class="active" href="/login.php">Giriş</a></li>
      <li><a href="/register.php">Kayıt</a></li>
    </ul>
  </nav>
</header>

<main class="container auth-wrap">
  <?= render_flash() ?>
  <div class="auth-card">
    <h1>Giriş Yap</h1>

    <form class="auth-form" method="post" action="/login.php" novalidate>
      <div>
        <label for="email">E-posta</label>
        <input id="email" name="email" type="email" required autocomplete="username" style="width:100%">
      </div>

      <div>
        <label for="password">Parola</label>
        <input id="password" name="password" type="password" required autocomplete="current-password" style="width:100%">
      </div>
      <button type="submit" class="btn">Giriş</button>

      <p class="muted">Hesabın yok mu? <a href="/register.php">Kayıt ol</a></p>
    </form>
  </div>
</main>

<footer class="site-footer"><small>© 2025</small></footer>
</body>
</html>
