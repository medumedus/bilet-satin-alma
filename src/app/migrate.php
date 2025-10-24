<?php
require_once __DIR__ . '/bootstrap.php';

$pdo = db();
$pdo->beginTransaction();

try {
  $schema = file_get_contents(__DIR__ . '/schema.sql');
  $pdo->exec($schema);

  $pdo->exec("INSERT OR IGNORE INTO firms (id, name) VALUES (1, 'Hızlı Turizm')");
  $pdo->exec("INSERT OR IGNORE INTO firms (id, name) VALUES (2, 'Anadolu Ekspres')");

  $adminPass = password_hash('123456', PASSWORD_BCRYPT);
  $compPass  = password_hash('123456', PASSWORD_BCRYPT);
  $userPass  = password_hash('123456', PASSWORD_BCRYPT);

  $stmt = $pdo->prepare("INSERT OR IGNORE INTO users (id, name, email, password_hash, role, firm_id, credit_balance_cents) 
                         VALUES (?, ?, ?, ?, ?, ?, ?)");
  $stmt->execute([1, 'Sistem Admin', 'admin@site.com', $adminPass, 'admin', null, 0]);
  $stmt->execute([2, 'Hızlı Turizm Yetkili', 'company@hizlitur.com', $compPass, 'company_admin', 1, 0]);
  $stmt->execute([3, 'Örnek Yolcu', 'user@example.com', $userPass, 'user', null, 20000]); // 200 TL kredi

  $now = new DateTimeImmutable('now');
  $d1 = $now->modify('+1 day')->setTime(10, 30)->format('Y-m-d H:i:s');
  $a1 = $now->modify('+1 day')->setTime(14, 0)->format('Y-m-d H:i:s');

  $d2 = $now->modify('+2 day')->setTime(18, 45)->format('Y-m-d H:i:s');
  $a2 = $now->modify('+2 day')->setTime(22, 15)->format('Y-m-d H:i:s');

  $stmt = $pdo->prepare("INSERT INTO trips (firm_id, origin, destination, departure_time, arrival_time, price_cents, total_seats)
                         VALUES (?, ?, ?, ?, ?, ?, ?)");
  $stmt->execute([1, 'İstanbul', 'Ankara', $d1, $a1, 35000, 40]);  
  $stmt->execute([2, 'İzmir', 'Bursa',   $d2, $a2, 28000, 36]);    

  $stmt = $pdo->prepare("INSERT OR IGNORE INTO coupons (code, discount_percent, firm_id, usage_limit, expires_at, is_active)
                         VALUES (?, ?, ?, ?, ?, 1)");
  $expires = (new DateTimeImmutable('+15 days'))->format('Y-m-d H:i:s');
  $stmt->execute(['GLOBAL10', 10, null, 500, $expires]);   
  $stmt->execute(['HIZLI15',  15, 1,    200, $expires]);   

  $pdo->commit();
  echo "Migration & seed tamam ✅\n";
} catch (Throwable $e) {
  $pdo->rollBack();
  echo "HATA: " . $e->getMessage() . "\n";
  exit(1);
}
