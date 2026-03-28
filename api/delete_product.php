<?php
require_once '../includes/auth.php';
require_once '../includes/functions.php';
header('Content-Type: application/json');
startSecureSession();

if (!isAdmin()) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Доступ запрещён']);
    exit;
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Метод не разрешён']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$id = intval($input['id'] ?? 0);
if ($id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Неверный ID']);
    exit;
}

$products = loadData(PRODUCTS_FILE);
$index = array_search($id, array_column($products, 'id'));
if ($index === false) {
    echo json_encode(['success' => false, 'message' => 'Товар не найден']);
    exit;
}
$oldFile = UPLOAD_DIR . $products[$index]['image'];
if (file_exists($oldFile)) unlink($oldFile);
array_splice($products, $index, 1);
saveData(PRODUCTS_FILE, $products);
echo json_encode(['success' => true]);