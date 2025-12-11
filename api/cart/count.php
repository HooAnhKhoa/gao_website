<?php
require_once __DIR__ . '/../../includes/init.php';

header('Content-Type: application/json');

$db = Database::getInstance();
$userId = $_SESSION['user_id'] ?? 0;

$cartCount = 0;

if ($userId > 0) {
    $result = $db->selectOne(
        "SELECT COUNT(*) as count FROM cart WHERE user_id = ?",
        [$userId]
    );
    $cartCount = $result['count'] ?? 0;
}

echo json_encode([
    'success' => true,
    'count' => $cartCount
]);