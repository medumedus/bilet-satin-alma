<?php
require_once __DIR__ . '/../app/bootstrap.php';
require_login();

$u = current_user();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  redirect('/account.php');
}

$ticket_id = (int)($_POST['ticket_id'] ?? 0);
$pdo = db();
$pdo->beginTransaction();

try {
  $st = $pdo->prepare("SELECT t.*, tr.departure_time FROM tickets t JOIN trips tr ON tr.id = t.trip_id WHERE t.id = ?");
  $st->execute([$ticket_id]);
  $t = $st->fetch();
  if (!$t) throw new Exception('Bilet bulunamadı.');
  if ((int)$t['user_id'] !== (int)$u['id']) throw new Exception('Bu bilet size ait değil.');
  if ($t['status'] !== 'purchased') throw new Exception('Bu bilet iptal edilemez.');

  $dep = new DateTimeImmutable($t['departure_time']);
  $now = new DateTimeImmutable('now');
  if (($dep->getTimestamp() - $now->getTimestamp()) < 3600) {
    throw new Exception('Kalkışa 1 saatten az kaldı; bilet iptal edilemez.');
  }

  $refund = (int)$t['price_paid_cents'];

  $st = $pdo->prepare("UPDATE tickets SET status = 'cancelled', cancelled_at = datetime('now') WHERE id = ?");
  $st->execute([$ticket_id]);
  $st = $pdo->prepare("UPDATE users SET credit_balance_cents = credit_balance_cents + ? WHERE id = ?");
  $st->execute([$refund, $u['id']]);

  $st = $pdo->prepare("INSERT INTO credit_transactions (user_id, amount_cents, reason, related_ticket_id) VALUES (?, ?, 'refund', ?)");
  $st->execute([$u['id'], $refund, $ticket_id]);

  $pdo->commit();

  $st = $pdo->prepare("SELECT * FROM users WHERE id = ?");
  $st->execute([$u['id']]);
  $_SESSION['user'] = $st->fetch();

  set_flash('success', 'Bilet iptal edildi. Ücret kredi bakiyenize iade edildi.');
  redirect('/account.php');
} catch (Throwable $e) {
  $pdo->rollBack();
  set_flash('error', $e->getMessage());
  redirect('/account.php');
}
