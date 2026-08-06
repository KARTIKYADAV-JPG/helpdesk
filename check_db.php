<?php

$host = '127.0.0.1';
$port = 3306;
$db   = 'helpdesk';
$user = 'root';
$pass = '';

// Step 1: Connect to MySQL server (no specific DB yet)
try {
    $dsn_server = "mysql:host={$host};port={$port}";
    $pdo = new PDO($dsn_server, $user, $pass, [PDO::ATTR_TIMEOUT => 5]);
    echo "[OK] PHP PDO driver: mysql" . PHP_EOL;
    echo "[OK] Connected to MySQL server at {$host}:{$port}" . PHP_EOL;
    echo "[OK] MySQL server version: " . $pdo->getAttribute(PDO::ATTR_SERVER_VERSION) . PHP_EOL;
} catch (PDOException $e) {
    echo "[FAIL] Cannot connect to MySQL server: " . $e->getMessage() . PHP_EOL;
    exit(1);
}

// Step 2: Check if database exists
$stmt = $pdo->query("SHOW DATABASES LIKE '{$db}'");
if ($stmt->rowCount() > 0) {
    echo "[OK] Database '{$db}' exists." . PHP_EOL;
} else {
    echo "[WARN] Database '{$db}' does NOT exist. Attempting to create it..." . PHP_EOL;
    try {
        $pdo->exec("CREATE DATABASE `{$db}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        echo "[OK] Database '{$db}' created successfully." . PHP_EOL;
    } catch (PDOException $e) {
        echo "[FAIL] Could not create database: " . $e->getMessage() . PHP_EOL;
        exit(1);
    }
}

// Step 3: Connect to the specific database
try {
    $dsn_db = "mysql:host={$host};port={$port};dbname={$db};charset=utf8mb4";
    $pdo_db = new PDO($dsn_db, $user, $pass, [PDO::ATTR_TIMEOUT => 5]);
    echo "[OK] Connected to database '{$db}' successfully." . PHP_EOL;
    echo PHP_EOL . "=== ALL CHECKS PASSED. Database connection is ready. ===" . PHP_EOL;
} catch (PDOException $e) {
    echo "[FAIL] Cannot connect to database '{$db}': " . $e->getMessage() . PHP_EOL;
    exit(1);
}
