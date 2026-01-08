<?php
// db.php (InfinityFree version)

$host = 'sql107.infinityfree.com';
$db   = 'if0_40712714_gps_attendance_bd';   // ✔ EXACT database name
$user = 'if0_40712714';
$pass = 'KHz7vjE3PZErL';

$charset = 'utf8mb4';
$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    die('Database connection failed: ' . $e->getMessage());
}
