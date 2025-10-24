<?php
require_once __DIR__ . '/../app/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $name = trim($_POST['name'] ?? '');
  $email = trim($_POST['email'] ?? '');
  $pass = $_POST['password'] ?? '';
  $pass2 = $_POST['password2'] ?? '';

  if ($name === '' || $email === '' || $pass === '') {
    set_flash('error', 'Lütfen tüm alanları doldurun.');
    redirect('/register.php');
  }
  if ($pass !== $pass2) {
    set_flash('error', 'Parolalar eşleşmiyor.');
    redirect('/register.php');
  }

  $id = register_user($name, $email, $pass);
  if (!$id) {
    set_flash('error', 'Bu e-posta zaten kayıtlı.');
    redirect('/register.php');
  }

  $user = find_user_by_email($email); // oto giriş
  login_user($user);
  set_flash('success', 'Kayıt başarılı, hoş geldiniz!');
  redirect('/');
}

$err = flash('error');
$ok  = flash('success');
?>
<!doctype html>
<html lang="tr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Kayıt Ol</title>
  <link rel="stylesheet" href="/assets/css/style.css?v=4">
</head>
<body>
  <main class="container" style="max-width:520px">
    <h1>Kayıt Ol</h1>
    <?php if($err): ?><p style="color:#fca5a5"><?=htmlspecialchars($err)?></p><?php endif; ?>
    <?php if($ok): ?><p style="color:#86efac"><?=htmlspecialchars($ok)?></p><?php endif; ?>

    <form method="post" style="display:grid;gap:.6rem;margin-top:1rem">
      <label>Ad Soyad
        <input name="name" required style="width:100%;padding:.5rem">
      </label>
      <label>E-posta
        <input type="email" name="email" required style="width:100%;padding:.5rem">
      </label>
      <label>Parola
        <input type="password" name="password" required style="width:100%;padding:.5rem">
      </label>
      <label>Parola (tekrar)
        <input type="password" name="password2" required style="width:100%;padding:.5rem">
      </label>
      <button class="btn" type="submit">Kayıt Ol</button>
      <p>Zaten hesabın var mı? <a href="/login.php">Giriş yap</a></p>
    </form>
  </main>
</body>
</html>
