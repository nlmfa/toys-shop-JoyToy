<?php
require_once 'includes/auth.php';
startSecureSession();

$userId = $_SESSION['user_id'] ?? 0;
$username = $_SESSION['username'] ?? '';

logEvent('logout', $userId, 'username=' . $username);

logout();
header('Location: index.php');
exit;