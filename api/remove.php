<?php
require_once '../../includes/auth.php';
require_once '../../includes/cart_functions.php';
header('Content-Type: application/json');
startSecureSession();

if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Необходимо войти в систему']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$productId = intval($input['product_id'] ?? 0);

if ($productId <= 0) {
    echo json_encode(['success' => false, 'message' => 'Неверный ID товара']);
    exit;
}

$userId = $_SESSION['user_id'];
removeFromCart($userId, $productId);

echo json_encode(['success' => true, 'message' => 'Товар удалён из корзины']);
?>