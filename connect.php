<?php
$serverName = $_SERVER['SERVER_NAME'] ?? 'localhost';

if ($serverName === 'localhost' || $serverName === '127.0.0.1') {
    $db_host = 'localhost';
    $db_user = 'root';
    $db_pass = '';
    $db_name = 'kitter';
} else {
    $db_host = 'localhost';
    $db_user = 'u801377270_petopiaph_2025';
    $db_pass = 'Petopiaph_2025';
    $db_name = 'u801377270_petopiaph_2025';
}

$dsn = "mysql:host=$db_host;dbname=$db_name;charset=utf8mb4";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

try {
    $pdo = new PDO($dsn, $db_user, $db_pass, $options);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>
