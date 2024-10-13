<?php
session_start();

// Проверяем, авторизован ли пользователь
$isLoggedIn = isset($_SESSION['username']);

// Подключение к базе данных
$mysqli = new mysqli("localhost", "admin", "0101", "travel_notes");

if ($mysqli->connect_error) {
    die("Ошибка подключения: " . $mysqli->connect_error);
}

// Запрос для получения всех постов
$query = "SELECT notes.*, users.username AS author FROM notes JOIN users ON notes.user_id = users.id ORDER BY notes.created_at DESC";
$result = $mysqli->query($query);
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Мой блог на PHP">
    <meta name="keywords" content="блог, php, статьи, новости">
    <meta name="author" content="Автор блога">
    <title>Мой блог</title>

    <!-- Подключение CSS -->
    <link rel="stylesheet" href="./css/styles.css">
    <style>
        /* Стили для изменения оформления постов */
        .post {
            border: 1px solid #ddd;
            padding: 20px;
            margin: 20px 0;
            border-radius: 5px;
            background: #f9f9f9;
        }

        .post h2 {
            font-size: 1.8em;
            margin-bottom: 15px;
        }

        .post img {
            display: block;
            margin: 15px auto; /* Центрирование изображения */
            max-width: 100%; /* Обрезка по размеру контейнера */
            height: auto; /* Автоматическая высота */
        }

        .post p {
            font-size: 1.2em;
            line-height: 1.6em;
        }

        .read-more {
            display: inline-block;
            margin-top: 15px;
            padding: 10px 15px;
            background: #333;
            color: #fff;
            text-decoration: none;
            border-radius: 5px;
        }

        .read-more:hover {
            background: #555;
        }
    </style>
</head>
<body>

    <!-- Шапка сайта -->
    <header>
        <h1>Путешествия</h1>
        <nav>
            <a href="index.php">Главная</a>
            <!-- Ссылка на страницу создания поста, видна только авторизованным пользователям -->
            <?php if ($isLoggedIn): ?>
                <a href="add_post.php">Создать пост</a>
                <a href="my_posts.php">Мои посты</a>
                <a href="edit_profile.php">Редактировать профиль</a>
                <a href="logout.php">Выйти</a>
            <?php else: ?>
                <a href="register.php">Регистрация</a>
                <a href="auth.php">Войти</a>
            <?php endif; ?>
        </nav>
    </header>

    <!-- Основной контейнер -->
    <div class="container">
        <!-- Основной контент страницы -->
        <div class="content">
            <?php if ($result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <div class="post">
                        <!-- Заголовок поста -->
                        <h2><?php echo htmlspecialchars($row['title']); ?></h2>

                        <!-- Картинка поста, если она есть -->
                        <?php if ($row['image']): ?>
                            <img src="images/<?php echo $row['image']; ?>" alt="Картинка поста">
                        <?php endif; ?>

                        <!-- Автор и дата публикации -->
                        <p><strong>Автор:</strong> <?php echo htmlspecialchars($row['author']); ?> | <strong>Дата:</strong> <?php echo htmlspecialchars($row['created_at']); ?></p>
                        
                        <!-- Краткое описание поста -->
                        <p><?php echo mb_strimwidth(htmlspecialchars($row['content']), 0, 200, "..."); ?></p>
                        
                        <!-- Ссылка на полный пост -->
                        <a href="view_notes.php?id=<?php echo $row['id']; ?>" class="read-more">Читать полностью</a>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p>Посты отсутствуют.</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Подвал сайта -->
    <footer>
        <p>&copy; 2024 Мой блог. Все права защищены.</p>
    </footer>

</body>
</html>
