<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';
require_once 'includes/cart_functions.php';
startSecureSession();

if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$currentUser = getCurrentUser();

logEvent('visit', $currentUser['id']);

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $old = $_POST['old_password'] ?? '';
    $new = $_POST['new_password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    if (empty($old) || empty($new) || empty($confirm)) {
        $error = 'Заполните все поля.';
    } elseif ($new !== $confirm) {
        $error = 'Новый пароль и подтверждение не совпадают.';
    } elseif (strlen($new) < 4) {
        $error = 'Пароль должен быть не менее 4 символов.';
    } else {
        $users = loadData(USERS_FILE);
        $userIndex = array_search($currentUser['id'], array_column($users, 'id'));
        if ($userIndex !== false && password_verify($old, $users[$userIndex]['password_hash'])) {
            $users[$userIndex]['password_hash'] = password_hash($new, PASSWORD_DEFAULT);
            saveData(USERS_FILE, $users);
            $message = 'Пароль успешно изменён.';
        } else {
            $error = 'Неверный текущий пароль.';
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_username'])) {
    $newUsername = trim($_POST['new_username'] ?? '');
    if (empty($newUsername)) {
        $error = 'Имя не может быть пустым.';
    } else {
        $users = loadData(USERS_FILE);
        $usernameExists = false;
        foreach ($users as $user) {
            if ($user['username'] === $newUsername && $user['id'] != $currentUser['id']) {
                $usernameExists = true;
                break;
            }
        }
        if ($usernameExists) {
            $error = 'Имя пользователя уже занято.';
        } else {
            $userIndex = array_search($currentUser['id'], array_column($users, 'id'));
            if ($userIndex !== false) {
                $users[$userIndex]['username'] = $newUsername;
                saveData(USERS_FILE, $users);
                $_SESSION['username'] = $newUsername;
                $currentUser = getCurrentUser();
                $message = 'Имя успешно изменено.';
            }
        }
    }
}

$allOrders = loadData(ORDERS_FILE);
$userOrders = array_filter($allOrders, function($order) use ($currentUser) {
    return $order['user_id'] == $currentUser['id'];
});
// Сортируем по дате (новые сверху)
usort($userOrders, function($a, $b) {
    return strtotime($b['date']) - strtotime($a['date']);
});
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Мой профиль</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <header class="navbar">
        <div class="container">
            <a href="index.php" class="logo">Магазин игрушек JoyToy</a>
            <nav>
                <a href="profile.php" class="profile-link">Привет, <?= htmlspecialchars($currentUser['username']) ?>!</a>
                <?php if ($currentUser['role'] === 'admin'): ?>
                    <a href="admin/dashboard.php" class="btn">Панель администратора</a>
                <?php endif; ?>
                <a href="cart.php" class="btn">🛒 Корзина</a>
                <a href="favorites.php" class="btn">❤️ Избранное</a>
                <a href="logout.php" class="btn">Выход</a>
            </nav>
        </div>
    </header>

    <main class="container">
        <div class="profile-grid">
            <div class="profile-left">
                <div class="form-container">
                    <h2>Мой профиль</h2>
                    
                    <?php if ($message): ?>
                        <div class="message" style="color: green;"><?= $message ?></div>
                    <?php endif; ?>
                    <?php if ($error): ?>
                        <div class="message" style="color: red;"><?= $error ?></div>
                    <?php endif; ?>

                    <h3>Редактировать имя</h3>
                    <form method="post">
                        <div class="form-group">
                            <label for="new_username">Новое имя пользователя</label>
                            <input type="text" id="new_username" name="new_username" value="<?= htmlspecialchars($currentUser['username']) ?>" required>
                        </div>
                        <button type="submit" name="change_username" class="btn btn-primary">Изменить имя</button>
                    </form>

                    <div class="profile-spacer"></div>

                    <h3>Смена пароля</h3>
                    <form method="post">
                        <div class="form-group">
                            <label for="old_password">Текущий пароль</label>
                            <input type="password" id="old_password" name="old_password" required>
                        </div>
                        <div class="form-group">
                            <label for="new_password">Новый пароль</label>
                            <input type="password" id="new_password" name="new_password" required>
                        </div>
                        <div class="form-group">
                            <label for="confirm_password">Подтверждение пароля</label>
                            <input type="password" id="confirm_password" name="confirm_password" required>
                        </div>
                        <button type="submit" name="change_password" class="btn btn-primary">Изменить пароль</button>
                    </form>
                </div>
            </div>

            <div class="profile-right">
                <div class="form-container">
                    <h2>История заказов</h2>
                    <?php if (empty($userOrders)): ?>
                        <div class="message" style="text-align: center; padding: 1rem;">У вас пока нет заказов.</div>
                    <?php else: ?>
                        <ul class="orders-list">
                            <?php foreach ($userOrders as $order): ?>
                                <li class="order-item">
                                    <div class="order-header">
                                        <span class="order-date"><?= date('d.m.Y H:i', strtotime($order['date'])) ?></span>
                                        <span class="order-total"><?= number_format($order['total'], 2, '.', ' ') ?> ₽</span>
                                    </div>
                                    <div class="order-method">
                                        Способ оплаты: <?= $order['method'] === 'cash' ? 'Наличные' : 'Карта' ?>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>
</body>
</html>