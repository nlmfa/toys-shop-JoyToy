<?php
require_once '../includes/auth.php';
require_once '../includes/functions.php';
require_once '../includes/cart_functions.php';
header('Content-Type: application/json');
startSecureSession();

if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Необходимо войти в систему']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$total = isset($input['total']) ? floatval($input['total']) : 0;
$method = isset($input['method']) ? trim($input['method']) : '';
$items = isset($input['items']) ? $input['items'] : [];

$userId = $_SESSION['user_id'];

saveOrder($userId, $total, $method, $items);

logEvent('order_created', $userId, "total=$total;method=$method");

clearUserCart($userId);

echo json_encode(['success' => true]);
