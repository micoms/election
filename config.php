<?php

declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

const DB_HOST = '127.0.0.1';
const DB_PORT = '3306';
const DB_NAME = 'election';
const DB_USER = 'root';
// Local development default only. Use DB_PASS environment variable in deployed environments.
const DB_PASS = '';

function db(): PDO
{
    static $pdo = null;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $host = getenv('DB_HOST') ?: DB_HOST;
    $port = getenv('DB_PORT') ?: DB_PORT;
    $name = getenv('DB_NAME') ?: DB_NAME;
    $user = getenv('DB_USER') ?: DB_USER;
    $pass = getenv('DB_PASS') ?: DB_PASS;

    $dsn = "mysql:host={$host};port={$port};dbname={$name};charset=utf8mb4";
    $pdo = new PDO(
        $dsn,
        $user,
        $pass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );

    return $pdo;
}

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function redirect(string $path): void
{
    header("Location: {$path}");
    exit;
}

function current_user(): ?array
{
    return $_SESSION['user'] ?? null;
}

function require_login(?string $role = null): array
{
    $user = current_user();
    if (!$user) {
        redirect('/index.php');
    }

    if ($role !== null && $user['role'] !== $role) {
        redirect($user['role'] === 'admin' ? '/pages/admin-dashboard.php' : '/pages/voter-dashboard.php');
    }

    return $user;
}
