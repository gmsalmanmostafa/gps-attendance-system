<?php
require_once 'config.php';
require_admin();
require_once 'db.php';

$id = (int)($_GET['id'] ?? 0);

// do not allow deleting admin
$pdo->prepare("DELETE FROM users WHERE id = ? AND role = 'employee'")->execute([$id]);

header('Location: admin_dashboard.php');
exit;
