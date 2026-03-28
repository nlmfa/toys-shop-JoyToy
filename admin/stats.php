<?php
require_once '../includes/auth.php';
startSecureSession();

if (!isAdmin()) {
    header('Location: ../login.php');
    exit;
}

$logFile = DATA_PATH . 'stats.log';
$events = [];

if (file_exists($logFile)) {
    $lines = file($logFile, FILE_IGNORE_NEW_LINES);
    $events = array_reverse($lines);
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Статистика посещений</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="stats-page">
    <h1>Статистика действий пользователей</h1>
    <div class="stats-filter">
        <label>Фильтр по типу: </label>
        <select id="typeFilter">
            <option value="">Все</option>
            <option value="visit">Посещения</option>
            <option value="registration">Регистрации</option>
            <option value="login">Входы</option>
            <option value="logout">Выходы</option>
            <option value="favorite_add">Добавление в избранное</option>
            <option value="cart_add">Добавление в корзину</option>
        </select>
    </div>
    <table class="stats-table" id="statsTable">
        <thead>
            <tr>
                <th>Тип</th>
                <th>Дата/время</th>
                <th>IP</th>
                <th>ID пользователя</th>
                <th>Доп. информация</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($events as $line): 
                $parts = explode(';', $line);
                if (count($parts) < 5) continue;
            ?>
                <tr class="event-row" data-type="<?= htmlspecialchars($parts[0]) ?>">
                    <td><?= htmlspecialchars($parts[0]) ?></td>
                    <td><?= htmlspecialchars($parts[1]) ?></td>
                    <td><?= htmlspecialchars($parts[2]) ?></td>
                    <td><?= htmlspecialchars($parts[3]) ?></td>
                    <td><?= htmlspecialchars($parts[4]) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <script>
        const filterSelect = document.getElementById('typeFilter');
        const rows = document.querySelectorAll('.event-row');
        filterSelect.addEventListener('change', function() {
            const selected = this.value;
            rows.forEach(row => {
                if (selected === '' || row.dataset.type === selected) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    </script>
</body>
</html>