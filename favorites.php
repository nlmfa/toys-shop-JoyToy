<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/cart_functions.php';
startSecureSession();

if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$userId = $_SESSION['user_id'];

logEvent('visit', $userId);

$favIds = getUserFavorites($userId);
$products = loadData(PRODUCTS_FILE);
$favItems = array_filter($products, function($p) use ($favIds) {
    return in_array($p['id'], $favIds);
});
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Избранное</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <header class="navbar">
        <div class="container">
            <a href="index.php" class="logo">Магазин игрушек JoyToy</a>
            <nav>
                <a href="profile.php" class="profile-link">Привет, <?= htmlspecialchars($_SESSION['username']) ?>!</a>
                <a href="cart.php" class="btn">🛒 Корзина</a>
                <a href="favorites.php" class="btn">❤️ Избранное</a>
                <a href="logout.php" class="btn">Выход</a>
            </nav>
        </div>
    </header>

    <main class="container">
        <h1 class="favorites-header">Избранное</h1>
        <?php if (empty($favItems)): ?>
            <p class="no-products">В избранном пока ничего нет.</p>
        <?php else: ?>
            <div class="favorites-grid">
                <?php foreach ($favItems as $item): ?>
                    <div class="product-card">
                        <a href="product.php?id=<?= $item['id'] ?>" style="text-decoration: none; color: inherit;">
                            <img src="assets/uploads/<?= $item['image'] ?>" alt="<?= htmlspecialchars($item['name']) ?>" style="width: 100%; height: 200px; object-fit: cover;">
                            <div class="product-info">
                                <h3><?= htmlspecialchars($item['name']) ?></h3>
                                <div class="price"><?= number_format($item['price'], 2, '.', ' ') ?> ₽</div>
                                <div>
                                    <span class="category">📦 <?= htmlspecialchars($item['category']) ?></span>
                                    <span class="age">👶 <?= htmlspecialchars($item['age'] ?? '0+') ?></span>
                                </div>
                            </div>
                        </a>
                        <div style="text-align: center; padding: 0 1rem 1rem;">
                            <button class="btn btn-danger" onclick="removeFromFavorites(<?= $item['id'] ?>)">Удалить из избранного</button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <footer class="footer">
            <p>📞 Телефон: +7 (391) 123-45-67</p>
            <p>📍 Адрес: г. Красноярск, ул. Мира, 10</p>
        </footer>
    </main>

    <script>
        async function removeFromFavorites(productId) {
            const response = await fetch('api/favorite/remove.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ product_id: productId })
            });
            const result = await response.json();
            if (result.success) {
                location.reload();
            } else {
                alert(result.message);
            }
        }
    </script>
</body>
</html>