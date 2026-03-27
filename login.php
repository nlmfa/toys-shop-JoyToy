<?php require_once 'includes/config.php'; ?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Вход</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <header class="navbar">
        <div class="container">
            <a href="index.php" class="logo">Магазин игрушек JoyToy</a>
            <nav>
                <a href="login.php" class="btn">Вход</a>
                <a href="register.php" class="btn">Регистрация</a>
            </nav>
        </div>
    </header>

    <main class="container">
        <div class="form-container">
            <h2>Вход</h2>
            <form id="loginForm">
                <div class="form-group">
                    <label for="username">Имя пользователя</label>
                    <input type="text" id="username" name="username" placeholder="Введите ваше имя" required>
                </div>
                <div class="form-group">
                    <label for="password">Пароль</label>
                    <input type="password" id="password" name="password" placeholder="Введите пароль" required>
                </div>
                <button type="submit" class="btn btn-primary">Войти</button>
                <div id="loginMessage" class="message"></div>
            </form>
            <p>Нет аккаунта? <a href="register.php">Зарегистрируйтесь</a></p>
        </div>
    </main>

    <script src="assets/js/script.js"></script>
    <script>
        document.getElementById('loginForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(e.target);
            const response = await fetch('api/login_check.php', { method: 'POST', body: formData });
            const result = await response.json();
            const msgDiv = document.getElementById('loginMessage');
            if (result.success) {
                window.location.href = result.redirect;
            } else {
                msgDiv.textContent = result.message;
                msgDiv.style.color = 'red';
            }
        });
    </script>
</body>
</html>