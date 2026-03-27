<?php
require_once '../includes/functions.php';
require_once '../includes/auth.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Метод не разрешён']);
    exit;
}

$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';
$confirm = $_POST['confirm_password'] ?? '';

if (empty($username) || empty($password)) {
    echo json_encode(['success' => false, 'message' => 'Заполните все поля']);
    exit;
}
if ($password !== $confirm) {
    echo json_encode(['success' => false, 'message' => 'Пароли не совпадают']);
    exit;
}
if (strlen($password) < 4) {
    echo json_encode(['success' => false, 'message' => 'Пароль должен быть не менее 4 символов']);
    exit;
}

$users = loadData(USERS_FILE);
foreach ($users as $u) {
    if ($u['username'] === $username) {
        echo json_encode(['success' => false, 'message' => 'Пользователь с таким именем уже существует']);
        exit;
    }
}

$newId = generateId($users);
$newUser = [
    'id' => $newId,
    'username' => $username,
    'password_hash' => password_hash($password, PASSWORD_DEFAULT),
    'role' => 'user',
    'created_at' => date('Y-m-d H:i:s')
];
$users[] = $newUser;
saveData(USERS_FILE, $users);

startSecureSession();
$_SESSION['user_id'] = $newId;
$_SESSION['username'] = $username;
$_SESSION['role'] = 'user';

logEvent('registration', $newId, 'username=' . $username);

echo json_encode(['success' => true, 'redirect' => 'index.php']);