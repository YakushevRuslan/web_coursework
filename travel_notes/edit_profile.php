<?php
session_start();
if (!isset($_SESSION['username'])) {
    // Если пользователь не авторизован, перенаправить на страницу входа
    header('Location: auth.php');
    exit;
}

require 'db.php'; // Подключение к базе данных

// Получение ID авторизованного пользователя из сессии
$user_id = $_SESSION['user_id'];

$message = ""; // Сообщение для отображения ошибок или успеха

try {
    // Запрос на получение данных текущего пользователя
    $query = "SELECT username, email FROM users WHERE id = :user_id";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        $message = "Пользователь не найден.";
        exit;
    }
} catch (PDOException $e) {
    $message = "Ошибка при получении данных пользователя: " . $e->getMessage();
}

// Обработка отправки формы
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Обновление данных профиля
    try {
        // Если пользователь ввел новый пароль, обновляем его
        if (!empty($password)) {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT); // Хеширование пароля
            $updateQuery = "UPDATE users SET username = :username, email = :email, password = :password WHERE id = :user_id";
            $updateStmt = $pdo->prepare($updateQuery);
            $updateStmt->bindParam(':username', $username);
            $updateStmt->bindParam(':email', $email);
            $updateStmt->bindParam(':password', $hashed_password);
        } else {
            // Если пароль не указан, обновляем только имя и email
            $updateQuery = "UPDATE users SET username = :username, email = :email WHERE id = :user_id";
            $updateStmt = $pdo->prepare($updateQuery);
            $updateStmt->bindParam(':username', $username);
            $updateStmt->bindParam(':email', $email);
        }

        $updateStmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);

        if ($updateStmt->execute()) {
            $message = "Профиль успешно обновлен.";
            // Обновляем сессию пользователя, если изменено имя
            $_SESSION['username'] = $username;
        } else {
            $message = "Ошибка при обновлении профиля.";
        }
    } catch (PDOException $e) {
        $message = "Ошибка при обновлении профиля: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Редактировать профиль</title>
    <link rel="stylesheet" href="css/styles.css"> <!-- Подключение общих стилей -->
    <link rel="stylesheet" href="css/add_post_styles.css"> <!-- Подключение стилей для формы редактирования -->
</head>
<body>
    <!-- Шапка сайта -->
    <header>
        <h1>Travel Notes. Редактирование профиля</h1>
        <nav>
            <a href="index.php">Главная</a>
            <a href="add_post.php">Создать пост</a>
            <a href="my_posts.php">Мои посты</a>
            <a href="edit_profile.php">Редактировать профиль</a>
            <a href="logout.php">Выйти</a>
        </nav>
    </header>

    <!-- Основной контейнер страницы -->
    <div class="container">
        <h2>Редактировать профиль</h2>
        <?php if ($message): ?>
            <p><?php echo htmlspecialchars($message); ?></p> <!-- Вывод сообщения об ошибке или успехе -->
        <?php endif; ?>

        <!-- Форма редактирования профиля -->
        <form class="post-form" method="POST" action="edit_profile.php">
            <div class="form-group">
                <label for="username">Имя пользователя:</label>
                <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required placeholder="Введите новое имя">
            </div>

            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required placeholder="Введите новый email">
            </div>

            <div class="form-group">
                <label for="password">Новый пароль:</label>
                <input type="password" id="password" name="password" placeholder="Введите новый пароль (если хотите сменить)">
            </div>

            <button type="submit" class="submit-btn">Сохранить изменения</button>
        </form>
    </div>

    <!-- Подвал сайта -->
    <footer>
        <p>&copy; 2024 Travel Notes. Курсовая работа.</p>
    </footer>
</body>
</html>
