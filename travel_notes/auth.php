<?php
session_start();
include('db.php');

// Проверка, авторизован ли пользователь
if (isset($_SESSION['username'])) {
    // Проверка роли пользователя
    if ($_SESSION['role'] === 'admin') {
        header("Location: admin.php"); // Перенаправление на админ-панель
    } else {
        header("Location: index.php"); // Перенаправление на главную страницу
    }
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    
    // Проверка на пустоту полей
    if (empty($username) || empty($password)) {
        $error = "Пожалуйста, заполните все поля.";
    } else {
        // Поиск пользователя в базе данных
        $stmt = $conn->prepare("SELECT id, password, role FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();

            // Проверка пароля
            if (password_verify($password, $user['password'])) {
                $_SESSION['username'] = $username;
                $_SESSION['user_id'] = $user['id']; // Сохраняем ID пользователя
                $_SESSION['role'] = $user['role']; // Сохраняем роль пользователя

                // Перенаправление в зависимости от роли
                if ($user['role'] === 'admin') {
                    header("Location: admin.php");
                } else {
                    header("Location: index.php");
                }
                exit();
            } else {
                $error = "Неверные имя пользователя или пароль.";
            }
        } else {
            $error = "Неверные имя пользователя или пароль.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Вход в систему</title>
    <link rel="stylesheet" href="./css/styles.css">
    <link rel="stylesheet" href="./css/register_styles.css"> <!-- Подключение стилей для регистрации -->
</head>
<body>
    <header>
        <h1>Мой блог</h1>
        <nav>
            <a href="index.php">Главная</a>
            <a href="register.php">Регистрация</a>
            <a href="auth.php">Войти</a>
        </nav>
    </header>

    <div class="container">
        <div class="registration-container"> <!-- Изменено название класса для единого стиля -->
            <h2>Вход в систему</h2>
            <?php if (isset($error)): ?>
                <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            <form action="auth.php" method="POST">
                <div class="form-group">
                    <label for="username">Имя пользователя:</label>
                    <input type="text" id="username" name="username" required>
                </div>
                <div class="form-group">
                    <label for="password">Пароль:</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <button type="submit">Войти</button>
                <p>Не зарегистрированы? <a href="register.php">Создайте аккаунт</a></p>
            </form>
        </div>
    </div>

    <footer>
        <p>&copy; 2024 Мой блог. Все права защищены.</p>
    </footer>
</body>
</html>
