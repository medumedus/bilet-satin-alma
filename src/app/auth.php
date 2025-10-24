<?php
require_once __DIR__ . '/db.php';

function current_user(): ?array {
  return $_SESSION['user'] ?? null;
}

function find_user_by_email(string $email): ?array {
  $pdo = db();
  $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
  $stmt->execute([$email]);
  $u = $stmt->fetch();
  return $u ?: null;
}

function register_user(string $name, string $email, string $password): ?int {
  $pdo = db();
  if (find_user_by_email($email)) return null; // e-posta benzersiz olmalı
  $hash = password_hash($password, PASSWORD_BCRYPT);
  $stmt = $pdo->prepare("INSERT INTO users (name, email, password_hash, role, credit_balance_cents) VALUES (?, ?, ?, 'user', 0)");
  $stmt->execute([$name, $email, $hash]);
  return (int)$pdo->lastInsertId();
}

function authenticate(string $email, string $password): ?array {
  $u = find_user_by_email($email);
  if (!$u) return null;
  if (!password_verify($password, $u['password_hash'])) return null;
  return $u;
}

function login_user(array $user): void { $_SESSION['user'] = $user; }
function logout_user(): void { unset($_SESSION['user']); }

function is_admin(): bool { return (current_user()['role'] ?? '') === 'admin'; }
function is_company_admin(): bool { return (current_user()['role'] ?? '') === 'company_admin'; }

function require_login(): void {
  if (!current_user()) {
    set_flash('error', 'Devam etmek için giriş yapmalısınız.');
    redirect('/login.php');
  }
}
function require_company_admin(): void {
  if (!is_company_admin()) {
    set_flash('error', 'Bu sayfaya erişmek için firma yetkilisi olmalısınız.');
    redirect('/');
  }
}
