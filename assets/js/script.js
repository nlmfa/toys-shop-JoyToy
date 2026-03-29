const categoryEmoji = {
    "Мягкие игрушки": "🧸",
    "Конструкторы": "🧱",
    "Машинки": "🚗",
    "Куклы": "🎎",
    "Развивающие": "🧩",
    "Настольные игры": "🎲",
    "Для малышей": "👶",
    "Электронные": "📱",
    "Спортивные": "⚽",
    "Творчество": "🎨"
};

async function loadProducts() {
    const search = document.getElementById('searchInput')?.value || '';
    const category = document.getElementById('categoryFilter')?.value || '';
    const age = document.getElementById('ageFilter')?.value || '';
    const url = `api/get_products.php?search=${encodeURIComponent(search)}&category=${encodeURIComponent(category)}&age=${encodeURIComponent(age)}`;
    const response = await fetch(url);
    const products = await response.json();
    const container = document.getElementById('productsContainer');
    if (!container) return;
    if (products.length === 0) {
        container.innerHTML = '<p class="no-products">Товары не найдены.</p>';
        return;
    }
    let html = '';
    products.forEach(p => {
        const emoji = categoryEmoji[p.category] ? categoryEmoji[p.category] + ' ' : '';
        html += `
            <div class="product-card" onclick="window.location.href='product.php?id=${p.id}'" style="cursor: pointer;">
                <img src="assets/uploads/${p.image}" alt="${p.name}">
                <div class="product-info">
                    <h3>${p.name}</h3>
                    <div class="price">${p.price.toFixed(2)} ₽</div>
                    <div class="category">${emoji}${p.category}</div>
                    <div class="age">👶 ${p.age || '0+'}</div>
                    <p>${p.description ? p.description.substring(0, 60) : ''}${p.description && p.description.length > 60 ? '…' : ''}</p>
                </div>
            </div>
        `;
    });
    container.innerHTML = html;
}

async function loadAdminProducts() {
    const response = await fetch('../api/get_products.php');
    const products = await response.json();
    const container = document.getElementById('productsTableContainer');
    if (!container) return;
    if (products.length === 0) {
        container.innerHTML = '<p class="admin-empty">Товаров нет.</p>';
        return;
    }
    let html = '<table class="table"><thead><tr><th>ID</th><th>Изображение</th><th>Название</th><th>Цена</th><th>Категория</th><th>Возраст</th><th>Действия</th></tr></thead><tbody>';
    products.forEach(p => {
        const emoji = categoryEmoji[p.category] ? categoryEmoji[p.category] + ' ' : '';
        const safeName = p.name ? p.name.replace(/"/g, '&quot;') : '';
        const safeDesc = p.description ? p.description.replace(/"/g, '&quot;') : '';
        html += `
            <tr>
                <td>${p.id}</td>
                <td><img src="../assets/uploads/${p.image}" width="50"></td>
                <td>${p.name}</td>
                <td>${p.price}</td>
                <td>${emoji}${p.category}</td>
                <td>👶 ${p.age || '0+'}</td>
                <td>
                    <button class="btn btn-primary edit-btn" 
                            data-id="${p.id}"
                            data-name="${safeName}"
                            data-price="${p.price}"
                            data-category="${p.category}"
                            data-description="${safeDesc}"
                            data-image="${p.image || ''}"
                            data-age="${p.age || '0+'}">
                        Редактировать
                    </button>
                    <button class="btn btn-danger" onclick="openDeleteModal(${p.id})">Удалить</button>
                </td>
            </tr>
        `;
    });
    html += '</tbody></table>';
    container.innerHTML = html;
}

document.addEventListener('click', function(e) {
    if (e.target.classList.contains('edit-btn')) {
        const btn = e.target;
        const product = {
            id: btn.dataset.id,
            name: btn.dataset.name,
            price: parseFloat(btn.dataset.price),
            category: btn.dataset.category,
            description: btn.dataset.description,
            image: btn.dataset.image,
            age: btn.dataset.age
        };
        if (typeof openProductModal === 'function') {
            openProductModal(product);
        } else {
            console.error('Функция openProductModal не найдена. Убедитесь, что она определена в admin/dashboard.php');
        }
    }
});
