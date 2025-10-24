<?php
require_once __DIR__ . '/../app/bootstrap.php';
require_login();

$u = current_user();
if (($u['role'] ?? '') !== 'user') {
  set_flash('error', 'Satın alma yalnızca User rolü için mümkündür.');
  redirect('/search.php');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  redirect('/search.php');
}

$trip_id     = (int)($_POST['trip_id'] ?? 0);
$seat_no     = (int)($_POST['seat_no'] ?? 0);
$coupon_code = strtoupper(trim($_POST['coupon'] ?? '')); 
$pdo = db();
$pdo->beginTransaction();

try {
  $st = $pdo->prepare("
    SELECT trips.*, firms.name AS firm_name
    FROM trips
    JOIN firms ON firms.id = trips.firm_id
    WHERE trips.id = ?
  ");
  $st->execute([$trip_id]);
  $trip = $st->fetch();
  if (!$trip) throw new Exception('Sefer bulunamadı.');

  $dep = new DateTimeImmutable($trip['departure_time']);
  if ($dep <= new DateTimeImmutable('now')) throw new Exception('Kalkış geçmiş. Satın alma yapılamaz.');

  if ($seat_no < 1 || $seat_no > (int)$trip['total_seats']) {
    throw new Exception('Geçersiz koltuk.');
  }

  $st = $pdo->prepare("SELECT COUNT(*) FROM tickets WHERE trip_id = ? AND seat_no = ? AND status = 'purchased'");
  $st->execute([$trip_id, $seat_no]);
  if ((int)$st->fetchColumn() > 0) throw new Exception('Bu koltuk zaten dolu.');

  $price_cents     = (int)$trip['price_cents'];
  $discount_cents  = 0;
  $applied_coupon  = null;
  if ($coupon_code !== '') {
    $st = $pdo->prepare("
      SELECT *
      FROM coupons
      WHERE code = ? AND firm_id = ? AND is_active = 1
    ");
    $st->execute([$coupon_code, (int)$trip['firm_id']]);
    $cp = $st->fetch();

    if ($cp) {
      $now    = new DateTimeImmutable('now');
      $okDate = empty($cp['expires_at']) || ($now <= new DateTimeImmutable($cp['expires_at']));
      $okUses = ((int)$cp['max_uses'] === 0) || ((int)$cp['used_count'] < (int)$cp['max_uses']);
      $okMin  = ((int)$price_cents >= (int)$cp['min_price_cents']);

      if ($okDate && $okUses && $okMin) {
        if ($cp['type'] === 'percent') {
          $discount_cents = (int) floor($price_cents * ((int)$cp['value']) / 100);
        } else { 
          $discount_cents = (int)$cp['value']; 
        }
        if ($discount_cents > $price_cents) $discount_cents = $price_cents;
        $applied_coupon = $cp; 
      } else {
       
      }
    } else {
     
    }
  }

  $final_cents = $price_cents - $discount_cents;
  $st = $pdo->prepare("SELECT credit_balance_cents FROM users WHERE id = ?");
  $st->execute([$u['id']]);
  $balance = (int)$st->fetchColumn();
  if ($balance < $final_cents) throw new Exception('Kredi bakiyesi yetersiz.');
  $st = $pdo->prepare("
    INSERT INTO tickets (user_id, trip_id, seat_no, status, price_paid_cents, coupon_code, discount_cents, purchased_at)
    VALUES (?, ?, ?, 'purchased', ?, ?, ?, datetime('now'))
  ");
  $st->execute([
    (int)$u['id'],
    $trip_id,
    $seat_no,
    $final_cents,
    ($applied_coupon ? $coupon_code : null),
    $discount_cents
  ]);
  $ticket_id = (int)$pdo->lastInsertId();

  $st = $pdo->prepare("UPDATE users SET credit_balance_cents = credit_balance_cents - ? WHERE id = ?");
  $st->execute([$final_cents, $u['id']]);

  $st = $pdo->prepare("
    INSERT INTO credit_transactions (user_id, amount_cents, reason, related_ticket_id)
    VALUES (?, ?, 'purchase', ?)
  ");
  $st->execute([$u['id'], -$final_cents, $ticket_id]);

  if ($applied_coupon && (int)$applied_coupon['max_uses'] !== 0) {
    $pdo->prepare("UPDATE coupons SET used_count = used_count + 1 WHERE id = ?")
        ->execute([(int)$applied_coupon['id']]);
  }

  $st = $pdo->prepare("SELECT * FROM users WHERE id = ?");
  $st->execute([$u['id']]);
  $_SESSION['user'] = $st->fetch();

  $pdo->commit();
  set_flash('success', 'Satın alma başarılı! Bilet no: #'.$ticket_id);
  redirect('/account.php');

} catch (Throwable $e) {
  $pdo->rollBack();
  set_flash('error', $e->getMessage());
  redirect('/trip.php?id=' . $trip_id);
}
