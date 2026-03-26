<?php
require_once 'config.php';

function loadData($file) {
    if (!file_exists($file)) return [];
    $content = file_get_contents($file);
    return json_decode($content, true) ?: [];
}

function saveData($file, $data) {
    $fp = fopen($file, 'w');
    if (flock($fp, LOCK_EX)) {
        fwrite($fp, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        flock($fp, LOCK_UN);
    }
    fclose($fp);
    return true;
}

function generateId($data) {
    if (empty($data)) return 1;
    $ids = array_column($data, 'id');
    return max($ids) + 1;
}

function cleanInput($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

function uploadImage($file) {
    $targetDir = UPLOAD_DIR;
    if (!is_dir($targetDir)) mkdir($targetDir, 0755, true);
    
    $imageFileType = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];
    if (!in_array($imageFileType, $allowedTypes))
        return ['success' => false, 'message' => 'Допустимы только JPG, JPEG, PNG, GIF.'];
    if ($file['size'] > 2 * 1024 * 1024)
        return ['success' => false, 'message' => 'Файл слишком большой (макс 2 МБ).'];
    
    $newFilename = uniqid() . '_' . time() . '.' . $imageFileType;
    $targetFile = $targetDir . $newFilename;
    if (move_uploaded_file($file['tmp_name'], $targetFile))
        return ['success' => true, 'filename' => $newFilename];
    return ['success' => false, 'message' => 'Ошибка загрузки файла.'];
}

function getProductById($id) {
    $products = loadData(PRODUCTS_FILE);
    foreach ($products as $p) {
        if ($p['id'] == $id) return $p;
    }
    return null;
}

function logEvent($eventType, $userId = 0, $extra = '') {
    $logFile = DATA_PATH . 'stats.log';
    $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    $date = date('Y-m-d H:i:s');
    $userId = intval($userId);
    $line = "$eventType;$date;$ip;$userId;$extra" . PHP_EOL;
    file_put_contents($logFile, $line, FILE_APPEND | LOCK_EX);
}
