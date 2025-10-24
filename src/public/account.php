<?php
require_once __DIR__ . '/../app/bootstrap.php';
require_login();

$u = current_user();
$pdo = db();

$sql = "SELECT t.*, tr.origin, tr.destination, tr.departure_time, tr.arrival_time,
               tr.price_cents AS trip_price_cents, tr.firm_id, f.name AS firm_name
        FROM tickets t
        JOIN trips tr ON tr.id = t.trip_id
        JOIN firms f ON f.id = tr.firm_id
        WHERE t.user_id = ?
        ORDER BY t.purchased_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute([$u['id']]);
$tickets = $stmt->fetchAll();

function money_tl(int $c): string { return number_format($c/100, 2, ',', '.') . ' TL'; }

$ok  = flash('success');
$err = flash('error');
?>
<!doctype html>
<html lang="tr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Hesabım</title>
  <link rel="stylesheet" href="/assets/css/style.css?v=4">
  <style>
    .card{background:#111827;padding:1rem;border-radius:.6rem;margin-top:1rem}
    .tickets{display:grid;gap:.6rem;margin-top:1rem}
    .row{display:flex;justify-content:space-between;gap:1rem;flex-wrap:wrap;align-items:flex-start}
    .muted{opacity:.85}

    .ticket-actions{
      display:grid;
      gap:.5rem;
      width:190px;             
      justify-items:stretch;
    }
    .ticket-actions .btn,
    .ticket-actions button,
    .ticket-actions a.btn{
      display:block;
      width:100%;
      text-align:center;
      padding:.55rem .8rem;
      line-height:1.2;
      white-space:nowrap;
    }
    .ticket-actions form{ margin:0; }

    .btn-outline{ background:#e8f0ff; }
    .btn-danger{ background:#fca5a5; border-color:#7f1d1d; }

    .small{ font-size:.92em; }
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

        <li><a class="active" href="/account.php">Hesabım</a></li>
        <li><a href="/logout.php">Çıkış</a></li>
      <?php else: ?>
        <li><a href="/login.php">Giriş</a></li>
        <li><a href="/register.php">Kayıt</a></li>
      <?php endif; ?>
    </ul>
  </nav>
</header>

<main class="container">
  <h1>Hesabım</h1>

  <?php if($ok): ?><p style="color:#86efac"><?=htmlspecialchars($ok)?></p><?php endif; ?>
  <?php if($err): ?><p style="color:#fca5a5"><?=htmlspecialchars($err)?></p><?php endif; ?>

  <div class="card">
    <p><strong>Ad:</strong> <?=htmlspecialchars($u['name'])?></p>
    <p><strong>E-posta:</strong> <?=htmlspecialchars($u['email'])?></p>
    <p><strong>Kredi:</strong> <?= money_tl((int)($u['credit_balance_cents'] ?? 0)) ?></p>
    <p style="margin-top:.5rem"><a class="btn" href="/search.php">Yeni Sefer Ara</a></p>
  </div>

  <h2 style="margin-top:1rem">Biletlerim</h2>
  <?php if (!$tickets): ?>
    <p class="muted">Henüz biletiniz yok.</p>
  <?php else: ?>
    <div class="tickets">
      <?php foreach ($tickets as $t):
        $dep = new DateTimeImmutable($t['departure_time']);
        $now = new DateTimeImmutable('now');
        $canCancel = $t['status'] === 'purchased' && ($dep->getTimestamp() - $now->getTimestamp()) >= 3600;
      ?>
        <div class="card">
          <div class="row">
            <div>
              <strong>#<?= (int)$t['id'] ?></strong> — <?=htmlspecialchars($t['firm_name'])?><br>
              <?=htmlspecialchars($t['origin'])?> → <?=htmlspecialchars($t['destination'])?>
              <div class="muted">
                Kalkış: <?=$dep->format('d.m.Y H:i')?> |
                Koltuk: <?= (int)$t['seat_no'] ?> |
                Durum: <?=htmlspecialchars(status_tr($t['status']))?>
              </div>
            </div>
            <div class="ticket-actions">
              <div class="paid"><strong>Ödenen:</strong> <?= money_tl((int)$t['price_paid_cents']) ?></div>

              <a class="btn" href="/ticket.php?id=<?= (int)$t['id'] ?>">Bilet Detayı</a>

              <?php if ($t['status'] === 'purchased'): ?>
                <a class="btn btn-outline" href="/ticket_pdf.php?id=<?= (int)$t['id'] ?>">PDF indir</a>
              <?php endif; ?>

              <?php if ($canCancel): ?>
                <form method="post" action="/cancel_ticket.php">
                  <input type="hidden" name="ticket_id" value="<?= (int)$t['id'] ?>">
                  <button class="btn btn-danger" type="submit">İptal Et</button>
                </form>
              <?php else: ?>
                <div class="muted small">
                  İptal koşulu: kalkıştan <strong>en az 1 saat önce</strong>.
                  <?php if ($t['status'] !== 'purchased'): ?> (Zaten <?=htmlspecialchars(status_tr($t['status']))?>)<?php endif; ?>
                </div>
              <?php endif; ?>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</main>
</body>
</html>
