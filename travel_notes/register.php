<?php
session_start(); // Начало сессии
require 'db.php'; // Подключение к базе данных

// Если пользователь уже авторизован, перенаправьте его
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// Обработка формы регистрации
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username']; // Получение имени пользователя
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Хеширование пароля
    $email = $_POST['email']; // Получение email
    $phone = $_POST['phone'] ?? ''; // Получение телефона, может быть пустым

    // Проверка на существование пользователя с таким именем или email
    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
    $stmt->bind_param("ss", $username, $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $error = "Пользователь с таким именем или email уже существует."; // Ошибка
    } else {
        // Вставка нового пользователя в базу данных
        $stmt = $conn->prepare("INSERT INTO users (username, password, email, phone) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $username, $password, $email, $phone);
        if ($stmt->execute()) {
            header("Location: auth.php"); // Переход на страницу авторизации
            exit();
        } else {
            $error = "Ошибка при регистрации, попробуйте снова."; // Ошибка вставки
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Регистрация на сайте">
    <meta name="keywords" content="регистрация, блог, php">
    <meta name="author" content="Автор блога">
    <title>Регистрация</title>

    <!-- Подключение CSS -->
    <link rel="stylesheet" href="./css/styles.css"> <!-- Подключение общих стилей -->
    <link rel="stylesheet" href="./css/register_styles.css"> <!-- Подключение стилей для регистрации -->
</head>
<body>
    <!-- Шапка сайта -->
    <header>
        <h1>Travel Blog</h1>
        <nav>
            <a href="index.php">Главная</a>
            <a href="auth.php">Войти</a>
        </nav>
    </header>

    <!-- Контейнер для регистрации -->
    <div class="container">
        <div class="registration-container">
            <h2>Регистрация</h2>
            <?php if (!empty($error)): ?>
                <div class="error-message"><?php echo htmlspecialchars($error); ?></div> <!-- Вывод ошибок -->
            <?php endif; ?>
            <form method="POST" action="">
                <div class="form-group">
                    <label for="username">Имя пользователя:</label>
                    <input type="text" id="username" name="username" required>
                </div>
                <div class="form-group">
                    <label for="password">Пароль:</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" required>
                </div>
                <div class="form-group">
                    <label for="phone">Телефон (необязательно):</label>
                    <input type="tel" id="phone" name="phone">
                </div>
                <button type="submit">Зарегистрироваться</button> <!-- Кнопка отправки -->
                <p>Уже зарегистрированы? <a href="auth.php">Войти</a></p>
            </form>
        </div>
    </div>

    <!-- Подвал сайта -->
    <footer>
        <p>&copy; 2024 Travel Notes. Курсовая работа.</p>
    </footer>
</body>
</html>
