<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/cart_functions.php';
startSecureSession();
$currentUser = getCurrentUser();

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

logEvent('visit', $currentUser ? $currentUser['id'] : 0, 'product_id=' . $id);

$product = getProductById($id);
if (!$product) {
    header('HTTP/1.0 404 Not Found');
    echo 'Товар не найден.';
    exit;
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($product['name']) ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <header class="navbar">
        <div class="container">
            <a href="index.php" class="logo">Магазин игрушек JoyToy</a>
            <nav>
                <?php if ($currentUser): ?>
                    <a href="profile.php" class="profile-link">Привет, <?= htmlspecialchars($currentUser['username']) ?>!</a>
                    <?php if ($currentUser['role'] === 'admin'): ?>
                        <a href="admin/dashboard.php" class="btn">Панель администратора</a>
                    <?php endif; ?>
                    <a href="cart.php" class="btn">🛒 Корзина</a>
                    <a href="favorites.php" class="btn">❤️ Избранное</a>
                    <a href="logout.php" class="btn">Выход</a>
                <?php else: ?>
                    <a href="login.php" class="btn">Вход</a>
                    <a href="register.php" class="btn">Регистрация</a>
                <?php endif; ?>
            </nav>
        </div>
    </header>

    <main class="container">
        <div class="product-detail">
            <img src="assets/uploads/<?= htmlspecialchars($product['image']) ?>" alt="<?= htmlspecialchars($product['name']) ?>">
            <div class="product-info-detail">
                <h1><?= htmlspecialchars($product['name']) ?></h1>
                <div class="price"><?= number_format($product['price'], 2, '.', ' ') ?> ₽</div>
                <div class="category">📦 Категория: <?= htmlspecialchars($product['category']) ?></div>
                <div class="age">👶 Возраст: <?= htmlspecialchars($product['age'] ?? '0+') ?></div>
                <div class="description"><?= nl2br(htmlspecialchars($product['description'])) ?></div>
                
                <div class="action-buttons">
                    <?php if ($currentUser): ?>
                        <button class="btn btn-primary" onclick="addToCart(<?= $product['id'] ?>)">🛒 В корзину</button>
                        <button class="btn btn-primary" onclick="addToFavorites(<?= $product['id'] ?>)">❤️ В избранное</button>
                    <?php else: ?>
                        <button class="btn btn-primary" onclick="alert('Пожалуйста, войдите в аккаунт, чтобы добавить товар в корзину')">🛒 В корзину</button>
                        <button class="btn btn-primary" onclick="alert('Пожалуйста, войдите в аккаунт, чтобы добавить товар в избранное')">❤️ В избранное</button>
                    <?php endif; ?>
                </div>
                <div id="actionMessage" class="message action-message"></div>
            </div>
        </div>

        <footer class="footer">
            <p>📞 Телефон: +7 (391) 123-45-67</p>
            <p>📍 Адрес: г. Красноярск, ул. Мира, 10</p>
        </footer>
    </main>

    <script>
        async function addToCart(productId) {
            const response = await fetch('api/cart/add.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ product_id: productId })
            });
            const result = await response.json();
            const msgDiv = document.getElementById('actionMessage');
            if (result.success) {
                msgDiv.innerHTML = '<span style="color: green;">✓ Товар добавлен в корзину</span>';
            } else {
                msgDiv.innerHTML = '<span style="color: red;">✗ ' + result.message + '</span>';
            }
        }

        async function addToFavorites(productId) {
            const response = await fetch('api/favorite/add.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ product_id: productId })
            });
            const result = await response.json();
            const msgDiv = document.getElementById('actionMessage');
            if (result.success) {
                msgDiv.innerHTML = '<span style="color: green;">✓ Товар добавлен в избранное</span>';
            } else {
                msgDiv.innerHTML = '<span style="color: red;">✗ ' + result.message + '</span>';
            }
        }
    </script>
</body>
</html>