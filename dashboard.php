<?php
require_once '../includes/auth.php';
startSecureSession();

if (!isAdmin()) {
    header('Location: ../login.php');
    exit;
}

$currentUser = getCurrentUser();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Управление товарами</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <header class="navbar">
        <div class="container">
            <a href="../index.php" class="logo">Магазин игрушек JoyToy</a>
            <nav>
                <a href="../profile.php" class="profile-link">Админ: <?= htmlspecialchars($currentUser['username']) ?></a>
                <a href="../index.php" class="btn">Главная</a>
                <a href="../logout.php" class="btn">Выход</a>
            </nav>
        </div>
    </header>

    <main class="container">
        <h1 class="admin-header">Панель администратора</h1>
        
        <div class="admin-button-container">
            <button class="btn btn-primary" id="addProductBtn">➕ Добавить товар</button>
        </div>
        
        <div id="productsTableContainer"></div>
    </main>

    <div id="productModal" class="modal" style="display: none;">
        <div class="modal-content">
            <span class="close" onclick="closeProductModal()">&times;</span>
            <h2 id="modalTitle">Добавить товар</h2>
            <form id="productForm" enctype="multipart/form-data">
                <input type="hidden" id="productId" name="id">
                <div class="form-group">
                    <label for="name">Название *</label>
                    <input type="text" id="name" name="name" required>
                </div>
                <div class="form-group">
                    <label for="price">Цена *</label>
                    <input type="number" step="0.01" id="price" name="price" required>
                </div>
                <div class="form-group">
                    <label for="category">Категория</label>
                    <select id="category" name="category">
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
                </div>
                <div class="form-group">
                    <label for="age">Возраст</label>
                    <select id="age" name="age">
                        <option value="0+">0+ (любой возраст)</option>
                        <option value="3+">3+</option>
                        <option value="5+">5+</option>
                        <option value="7+">7+</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="description">Описание</label>
                    <textarea id="description" name="description" rows="4"></textarea>
                </div>
                <div class="form-group">
                    <label for="image">Изображение</label>
                    <input type="file" id="image" name="image" accept="image/*">
                    <div id="currentImage"></div>
                </div>
                <div id="formMessage" class="message"></div>
                <button type="button" class="btn btn-primary" onclick="saveProduct()">Сохранить</button>
                <button type="button" class="btn" onclick="closeProductModal()">Отмена</button>
            </form>
        </div>
    </div>

    <div id="deleteModal" class="modal" style="display: none;">
        <div class="modal-content">
            <h3>Подтверждение удаления</h3>
            <p>Вы уверены, что хотите удалить этот товар?</p>
            <button class="btn btn-danger" onclick="confirmDelete()">Удалить</button>
            <button class="btn" onclick="closeDeleteModal()">Отмена</button>
        </div>
    </div>

    <script src="../assets/js/script.js"></script>
    <script>
        let currentDeleteId = null;

        function openProductModal(product = null) {
            document.getElementById('productForm').reset();
            document.getElementById('productId').value = '';
            document.getElementById('currentImage').innerHTML = '';
            document.getElementById('modalTitle').textContent = 'Добавить товар';
            document.getElementById('formMessage').textContent = '';

            if (product) {
                document.getElementById('modalTitle').textContent = 'Редактировать товар';
                document.getElementById('productId').value = product.id;
                document.getElementById('name').value = product.name;
                document.getElementById('price').value = product.price;
                document.getElementById('category').value = product.category;
                document.getElementById('age').value = product.age || '0+';
                document.getElementById('description').value = product.description;
                if (product.image) {
                    document.getElementById('currentImage').innerHTML = `<img src="../assets/uploads/${product.image}" width="100">`;
                }
            }
            document.getElementById('productModal').style.display = 'block';
        }

        function closeProductModal() {
            document.getElementById('productModal').style.display = 'none';
        }

        function openDeleteModal(id) {
            currentDeleteId = id;
            document.getElementById('deleteModal').style.display = 'block';
        }

        function closeDeleteModal() {
            document.getElementById('deleteModal').style.display = 'none';
        }

        async function saveProduct() {
            const form = document.getElementById('productForm');
            const formData = new FormData(form);
            const id = document.getElementById('productId').value;
            const url = id ? '../api/update_product.php' : '../api/add_product.php';

            const response = await fetch(url, { method: 'POST', body: formData });
            const result = await response.json();
            if (result.success) {
                closeProductModal();
                loadAdminProducts();
            } else {
                document.getElementById('formMessage').textContent = result.message;
            }
        }

        async function confirmDelete() {
            if (!currentDeleteId) return;
            const response = await fetch('../api/delete_product.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id: currentDeleteId })
            });
            const result = await response.json();
            if (result.success) {
                closeDeleteModal();
                loadAdminProducts();
            } else {
                alert(result.message);
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            loadAdminProducts();
            document.getElementById('addProductBtn').addEventListener('click', () => openProductModal());
        });
    </script>
</body>
</html>