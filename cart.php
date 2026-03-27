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

$cartIds = getUserCart($userId);
$products = loadData(PRODUCTS_FILE);
$cartItems = array_filter($products, function($p) use ($cartIds) {
    return in_array($p['id'], $cartIds);
});
$total = 0;
foreach ($cartItems as $item) {
    $total += $item['price'];
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Корзина</title>
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
        <h1 class="cart-header">Корзина</h1>
        <?php if (empty($cartItems)): ?>
            <p class="no-products">Корзина пуста.</p>
        <?php else: ?>
            <div class="cart-items">
                <?php foreach ($cartItems as $item): ?>
                    <div class="cart-item">
                        <img src="assets/uploads/<?= $item['image'] ?>" alt="<?= htmlspecialchars($item['name']) ?>">
                        <div class="item-info">
                            <h3><a href="product.php?id=<?= $item['id'] ?>"><?= htmlspecialchars($item['name']) ?></a></h3>
                            <div>
                                <span class="category">📦 <?= htmlspecialchars($item['category']) ?></span>
                                <span class="age">👶 <?= htmlspecialchars($item['age'] ?? '0+') ?></span>
                            </div>
                            <p class="price"><?= number_format($item['price'], 2, '.', ' ') ?> ₽</p>
                        </div>
                        <button class="btn btn-danger" onclick="removeFromCart(<?= $item['id'] ?>)">Удалить</button>
                    </div>
                <?php endforeach; ?>
                <div class="cart-total">
                    Итого: <span><?= number_format($total, 2, '.', ' ') ?> ₽</span>
                </div>
                <div class="order-button">
                    <button class="btn btn-primary" id="checkoutBtn">Оформить заказ</button>
                </div>
            </div>
        <?php endif; ?>

        <footer class="footer">
            <p>📞 Телефон: +7 (391) 123-45-67</p>
            <p>📍 Адрес: г. Красноярск, ул. Мира, 10</p>
        </footer>
    </main>

    <!-- Модальное окно оформления заказа -->
    <div id="checkoutModal" class="checkout-modal">
        <div class="modal-content">
            <span class="close" id="closeCheckoutModal">&times;</span>
            <div id="checkoutContent">
                <h3>Выберите способ оплаты</h3>
                <div class="payment-options">
                    <button class="btn btn-primary" id="cashBtn">Наличные</button>
                    <button class="btn btn-primary" id="cardBtn">Карта</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        const totalAmount = <?= $total ?>;
        const cartItems = <?= json_encode(array_map(function($item) {
            return [
                'product_id' => $item['id'],
                'name' => $item['name'],
                'price' => $item['price'],
                'quantity' => 1
            ];
        }, array_values($cartItems))) ?>;

        async function removeFromCart(productId) {
            const response = await fetch('api/cart/remove.php', {
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

        async function logOrder(method) {
            try {
                await fetch('api/order.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        total: totalAmount,
                        method: method,
                        items: cartItems
                    })
                });
            } catch (err) {
                console.error('Ошибка логирования заказа', err);
            }
        }

        const modal = document.getElementById('checkoutModal');
        const checkoutBtn = document.getElementById('checkoutBtn');
        const closeModal = document.getElementById('closeCheckoutModal');
        const contentDiv = document.getElementById('checkoutContent');

        function showModal() {
            modal.style.display = 'flex';
            resetToPayment();
        }

        function hideModal() {
            modal.style.display = 'none';
        }

        function resetToPayment() {
            contentDiv.innerHTML = `
                <h3>Выберите способ оплаты</h3>
                <div class="payment-options">
                    <button class="btn btn-primary" id="cashBtn">Наличные</button>
                    <button class="btn btn-primary" id="cardBtn">Карта</button>
                </div>
            `;
            attachPaymentHandlers();
        }

        function showCardForm() {
            contentDiv.innerHTML = `
                <h3>Данные карты</h3>
                <div class="card-form">
                    <div class="form-group">
                        <label>Номер карты</label>
                        <input type="text" id="cardNumber" placeholder="1234 5678 9012 3456" maxlength="19" autocomplete="off">
                    </div>
                    <div class="form-group">
                        <label>Срок действия</label>
                        <input type="text" id="cardExpiry" placeholder="ММ/ГГ" maxlength="5" autocomplete="off">
                    </div>
                    <div class="form-group">
                        <label>CVV</label>
                        <input type="text" id="cardCvv" placeholder="123" maxlength="3" autocomplete="off">
                    </div>
                    <button class="btn btn-primary" id="submitCardBtn">Оплатить</button>
                </div>
            `;
            attachCardHandlers();
        }

        async function processOrder(method) {
            await logOrder(method);
            showMessage('Ваш заказ будет ждать вас в магазине. Спасибо за покупку!');
        }

        function attachPaymentHandlers() {
            const cashBtn = document.getElementById('cashBtn');
            const cardBtn = document.getElementById('cardBtn');

            if (cashBtn) {
                cashBtn.addEventListener('click', () => {
                    processOrder('cash');
                });
            }
            if (cardBtn) {
                cardBtn.addEventListener('click', () => {
                    showCardForm();
                });
            }
        }

        function attachCardHandlers() {
            const cardNumberInput = document.getElementById('cardNumber');
            const cardExpiryInput = document.getElementById('cardExpiry');
            const cardCvvInput = document.getElementById('cardCvv');
            const submitBtn = document.getElementById('submitCardBtn');

            cardNumberInput.addEventListener('input', function(e) {
                let value = e.target.value.replace(/\D/g, '');
                if (value.length > 16) value = value.slice(0, 16);
                let formatted = value.replace(/(\d{4})(?=\d)/g, '$1 ');
                e.target.value = formatted;
            });

            cardExpiryInput.addEventListener('input', function(e) {
                let value = e.target.value.replace(/\D/g, '');
                if (value.length > 4) value = value.slice(0, 4);
                if (value.length > 2) {
                    e.target.value = value.slice(0, 2) + '/' + value.slice(2, 4);
                } else {
                    e.target.value = value;
                }
            });

            cardCvvInput.addEventListener('input', function(e) {
                e.target.value = e.target.value.replace(/\D/g, '').slice(0, 3);
            });

            submitBtn.addEventListener('click', () => {
                const cardNumber = document.getElementById('cardNumber').value.replace(/\s/g, '');
                const expiry = document.getElementById('cardExpiry').value;
                const cvv = document.getElementById('cardCvv').value;

                if (!cardNumber || !expiry || !cvv) {
                    showMessage('Пожалуйста, заполните все поля карты.');
                    return;
                }
                if (cardNumber.length !== 16) {
                    showMessage('Номер карты должен содержать 16 цифр.');
                    return;
                }
                if (expiry.length !== 5 || expiry.indexOf('/') === -1) {
                    showMessage('Срок действия должен быть в формате ММ/ГГ.');
                    return;
                }
                if (cvv.length !== 3) {
                    showMessage('CVV должен содержать 3 цифры.');
                    return;
                }

                processOrder('card');
            });
        }

        function showMessage(message) {
            contentDiv.innerHTML = `
                <div class="message-box">
                    <p>${message}</p>
                    <button class="btn btn-primary" id="closeMessageBtn">Закрыть</button>
                </div>
            `;
            const closeBtn = document.getElementById('closeMessageBtn');
            if (closeBtn) {
                closeBtn.addEventListener('click', () => {
                    hideModal();
                    location.reload();
                });
            }
        }

        if (checkoutBtn) {
            checkoutBtn.addEventListener('click', showModal);
        }
        closeModal.addEventListener('click', hideModal);
        window.addEventListener('click', (event) => {
            if (event.target == modal) hideModal();
        });
    </script>
</body>
</html>