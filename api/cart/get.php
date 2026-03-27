<?php
require_once '../../includes/auth.php';
require_once '../../includes/cart_functions.php';
require_once '../../includes/functions.php';
header('Content-Type: application/json');
startSecureSession();

if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Необходимо войти в систему']);
    exit;
}

$userId = $_SESSION['user_id'];
$cartIds = getUserCart($userId);
$products = loadData(PRODUCTS_FILE);
$cartItems = array_filter($products, function($p) use ($cartIds) {
    return in_array($p['id'], $cartIds);
});

echo json_encode(array_values($cartItems));
?>
