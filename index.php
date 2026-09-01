<?php
$message = "";

// 1. Подключение и создание базы данных SQLite
// Ели файла database.db нет, PHP создаст его автоматически в этой же папке
try {
    $pdo = new PDO('sqlite:database.db');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 2. Создание таблицы, если она еще не существует
    $query = "CREATE TABLE IF NOT EXISTS users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL,
        email TEXT NOT NULL
    )";
    $pdo->exec($query);

} catch (PDOException $e) {
    die("Ошибка подключения к БД: " . $e->getMessage());
}

// 3. Обработка отправки формы
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');

    if (!empty($name) && !empty($email)) {
        // 4. Вставка данных в базу SQLite с помощью подготовленного запроса
        $stmt = $pdo->prepare("INSERT INTO users (name, email) VALUES (:name, :email)");
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':email', $email);

        if ($stmt->execute()) {
            $message = "<p style='color: green;'>Данные успешно сохранены в базу SQLite!</p>";
        } else {
            $message = "<p style='color: red;'>Ошибка при сохранении данных.</p>";
        }
    } else {
        $message = "<p style='color: orange;'>Пожалуйста, заполните все поля.</p>";
    }
}

// Получаем список сохраненных записей для проверки
$users = $pdo->query("SELECT * FROM users ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Форма с базой данных SQLite</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; line-height: 1.6; }
        form { background: #f4f4f4; padding: 20px; border-radius: 8px; max-width: 400px; }
        div { margin-bottom: 15px; }
        label { display: block; font-weight: bold; }
        input[type="text"], input[type="email"] { width: 100%; padding: 8px; box-sizing: border-box; }
        button { padding: 10px 15px; background: #28a745; color: white; border: none; cursor: pointer; border-radius: 4px; }
        table { border-collapse: collapse; margin-top: 20px; width: 100%; max-width: 500px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>

    <h2>Форма добавления пользователя</h2>
    
    <?= $message ?>

    <!-- HTML-форма ввода данных -->
    <form action="index.php" method="POST">
        <div>
            <label for="name">Имя:</label>
            <input type="text" id="name" name="name" required>
        </div>
        <div>
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required>
        </div>
        <button type="submit">Сохранить в БД</button>
    </form>

    <h2>Записи в SQLite:</h2>
    <table>
        <tr>
            <th>ID</th>
            <th>Имя</th>
            <th>Email</th>
        </tr>
        <?php foreach ($users as $user): ?>
            <tr>
                <td><?= htmlspecialchars($user['id']) ?></td>
                <td><?= htmlspecialchars($user['name']) ?></td>
                <td><?= htmlspecialchars($user['email']) ?></td>
            </tr>
        <?php endforeach; ?>
    </table>

</body>
</html>