<?php
require_once 'config.php';
require_login();
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo "Invalid request.";
    exit;
}

$type = $_POST['type'] ?? '';
$lat  = $_POST['lat'] ?? '';
$lon  = $_POST['lon'] ?? '';

if (!in_array($type, ['IN','OUT'], true)) {
    echo "Invalid attendance type.";
    exit;
}

if ($lat === '' || $lon === '') {
    echo "Location not found. Please try again.";
    exit;
}

$user_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("
    INSERT INTO attendance (user_id, check_type, latitude, longitude)
    VALUES (?, ?, ?, ?)
");
$stmt->execute([$user_id, $type, $lat, $lon]);

echo "Attendance " . htmlspecialchars($type) . " saved successfully!";
