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

$name = trim($_POST['name'] ?? '');
$price = floatval($_POST['price'] ?? 0);
$category = trim($_POST['category'] ?? '');
$description = trim($_POST['description'] ?? '');
$age = trim($_POST['age'] ?? '');

if (empty($name) || $price <= 0) {
    echo json_encode(['success' => false, 'message' => 'Название и корректная цена обязательны']);
    exit;
}

if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'message' => 'Изображение обязательно']);
    exit;
}
$upload = uploadImage($_FILES['image']);
if (!$upload['success']) {
    echo json_encode($upload);
    exit;
}
$imageFilename = $upload['filename'];

$products = loadData(PRODUCTS_FILE);
$newId = generateId($products);
$newProduct = [
    'id' => $newId,
    'name' => $name,
    'price' => $price,
    'category' => $category,
    'description' => $description,
    'image' => $imageFilename,
    'age' => $age ?: '0+',
    'created_at' => date('Y-m-d H:i:s')
];
$products[] = $newProduct;
saveData(PRODUCTS_FILE, $products);
echo json_encode(['success' => true, 'product' => $newProduct]);