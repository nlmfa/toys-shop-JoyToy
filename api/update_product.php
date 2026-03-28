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

$id = intval($_POST['id'] ?? 0);
$name = trim($_POST['name'] ?? '');
$price = floatval($_POST['price'] ?? 0);
$category = trim($_POST['category'] ?? '');
$description = trim($_POST['description'] ?? '');
$age = trim($_POST['age'] ?? '');

if ($id <= 0 || empty($name) || $price <= 0) {
    echo json_encode(['success' => false, 'message' => 'Некорректные данные']);
    exit;
}

$products = loadData(PRODUCTS_FILE);
$index = array_search($id, array_column($products, 'id'));
if ($index === false) {
    echo json_encode(['success' => false, 'message' => 'Товар не найден']);
    exit;
}

$imageFilename = $products[$index]['image'];
if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
    $upload = uploadImage($_FILES['image']);
    if (!$upload['success']) {
        echo json_encode($upload);
        exit;
    }
    $oldFile = UPLOAD_DIR . $products[$index]['image'];
    if (file_exists($oldFile)) unlink($oldFile);
    $imageFilename = $upload['filename'];
}

$products[$index] = [
    'id' => $id,
    'name' => $name,
    'price' => $price,
    'category' => $category,
    'description' => $description,
    'image' => $imageFilename,
    'age' => $age ?: $products[$index]['age'],
    'created_at' => $products[$index]['created_at']
];
saveData(PRODUCTS_FILE, $products);
echo json_encode(['success' => true, 'product' => $products[$index]]);