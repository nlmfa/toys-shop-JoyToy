<?php
require_once '../includes/auth.php';
header('Content-Type: application/json');
startSecureSession();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Метод не разрешён']);
    exit;
}

$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';

if (login($username, $password)) {
    $role = $_SESSION['role'];
    $redirect = ($role === 'admin') ? 'admin/dashboard.php' : 'index.php';

    logEvent('login', $_SESSION['user_id'], 'username=' . $username);

    echo json_encode(['success' => true, 'role' => $role, 'redirect' => $redirect]);
} else {
    echo json_encode(['success' => false, 'message' => 'Неверный логин или пароль']);
}