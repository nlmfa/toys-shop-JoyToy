<?php require_once 'includes/config.php'; ?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Регистрация</title>
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
            <h2>Регистрация</h2>
            <form id="registerForm">
                <div class="form-group">
                    <label for="username">Имя пользователя</label>
                    <input type="text" id="username" name="username" placeholder="Введите ваше имя" required>
                </div>
                <div class="form-group">
                    <label for="password">Пароль</label>
                    <input type="password" id="password" name="password" placeholder="Введите пароль" required>
                </div>
                <div class="form-group">
                    <label for="confirm_password">Подтверждение пароля</label>
                    <input type="password" id="confirm_password" name="confirm_password" placeholder="Повторите пароль" required>
                </div>
                
                <div class="checkbox-group">
                    <input type="checkbox" id="consent" name="consent">
                    <label for="consent" class="checkmark"></label>
                    <span>Я согласен(на) на обработку персональных данных</span>
                </div>

                <button type="submit" class="btn btn-primary">Зарегистрироваться</button>
                <div id="registerMessage" class="message"></div>
            </form>
            <p>Уже есть аккаунт? <a href="login.php">Войдите</a></p>
        </div>
    </main>

    <script src="assets/js/script.js"></script>
    <script>
        document.getElementById('registerForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const password = document.getElementById('password').value;
            const consent = document.getElementById('consent').checked;
            const msgDiv = document.getElementById('registerMessage');
            
            let errors = [];
            if (password.length < 4) {
                errors.push('Пароль должен быть не менее 4 символов.');
            }
            if (!consent) {
                errors.push('Необходимо согласие на обработку персональных данных.');
            }
            
            if (errors.length > 0) {
                msgDiv.innerHTML = `<span style="color: red;">${errors.join(' ')}</span>`;
                return;
            }
            
            const formData = new FormData(e.target);
            formData.delete('consent');
            const response = await fetch('api/register.php', { method: 'POST', body: formData });
            const result = await response.json();
            
            if (result.success) {
                window.location.href = result.redirect;
            } else {
                msgDiv.innerHTML = `<span style="color: red;">${result.message}</span>`;
            }
        });
    </script>
</body>
</html>