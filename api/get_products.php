<?php
require_once '../includes/functions.php';
header('Content-Type: application/json');

$products = loadData(PRODUCTS_FILE);
$search = trim($_GET['search'] ?? '');
$category = $_GET['category'] ?? '';
$age = $_GET['age'] ?? '';

$filtered = array_filter($products, function($p) use ($search, $category, $age) {
    if ($search && mb_stripos($p['name'], $search) === false) return false;
    if ($category && $p['category'] !== $category) return false;
    if ($age && $p['age'] !== $age) return false;
    return true;
});

echo json_encode(array_values($filtered));