<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';
startSecureSession();
$currentUser = getCurrentUser();

logEvent('visit', $currentUser ? $currentUser['id'] : 0);
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Магазин игрушек JoyToy</title>
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
        <div class="filters">
            <input type="text" id="searchInput" placeholder="Поиск по названию...">
            <select id="categoryFilter">
                <option value="">Все категории</option>
                <option value="Мягкие игрушки">🧸 Мягкие игрушки</option>
                <option value="Конструкторы">🧱 Конструкторы</option>
                <option value="Машинки">🚗 Машинки</option>
                <option value="Куклы">🎎 Куклы</option>
                <option value="Развивающие">🧩 Развивающие</option>
                <option value="Настольные игры">🎲 Настольные игры</option>
                <option value="Для малышей">👶 Для малышей</option>
                <option value="Электронные">📱 Электронные</option>
                <option value="Спортивные">⚽ Спортивные</option>
                <option value="Творчество">🎨 Творчество</option>
            </select>
            <select id="ageFilter">
                <option value="">Любой возраст</option>
                <option value="0+">👶 0+</option>
                <option value="3+">🧒 3+</option>
                <option value="5+">👧 5+</option>
                <option value="7+">🧑 7+</option>
            </select>
        </div>

        <div id="productsContainer" class="products-grid">
            <div class="loading">Загрузка товаров...</div>
        </div>

        <footer class="footer">
            <p>📞 Телефон: +7 (391) 123-45-67</p>
            <p>📍 Адрес: г. Красноярск, ул. Мира, 10</p>
        </footer>
    </main>

    <script src="assets/js/script.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            loadProducts();
            document.getElementById('searchInput').addEventListener('input', loadProducts);
            document.getElementById('categoryFilter').addEventListener('change', loadProducts);
            document.getElementById('ageFilter').addEventListener('change', loadProducts);
        });
    </script>
</body>
</html>