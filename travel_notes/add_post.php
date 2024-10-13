<?php
session_start();
include('db.php');

// Проверка авторизации
if (!isset($_SESSION['username'])) {
    header("Location: auth.php");
    exit();
}

$message = ""; // Сообщение для отображения ошибок или успеха

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $_POST['title'];
    $content = $_POST['content'];
    $user_id = $_SESSION['user_id'];

    // Обработка загрузки изображения
    $image = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $image = basename($_FILES['image']['name']);
        $target_dir = "images/";
        if (move_uploaded_file($_FILES['image']['tmp_name'], $target_dir . $image)) {
            $message = "Изображение успешно загружено.";
        } else {
            $message = "Ошибка при загрузке изображения: не удалось переместить файл.";
        }
    } else {
        $message = "Ошибка при загрузке изображения: " . $_FILES['image']['error'];
    }

    // Сохранение поста в базе данных
    if ($image !== null) { // Только если изображение загружено
        $stmt = $mysqli->prepare("INSERT INTO notes (title, content, user_id, image) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssis", $title, $content, $user_id, $image);
        if ($stmt->execute()) {
            $message = "Пост успешно добавлен.";
            header("Location: my_posts.php");
            exit();
        } else {
            $message = "Ошибка при добавлении поста: " . $stmt->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Добавление нового поста">
    <meta name="keywords" content="блог, добавить пост, php">
    <meta name="author" content="Автор блога">
    <title>Добавить пост</title>
    <link rel="stylesheet" href="css/styles.css"> <!-- Подключение общих стилей -->
    <link rel="stylesheet" href="css/add_post_styles.css"> <!-- Подключение стилей для добавления поста -->
</head>
<body>
    <!-- Шапка сайта -->
    <header>
        <h1>Travel Notes</h1>
        <nav>
            <a href="index.php">Главная</a>
            <a href="add_post.php">Создать пост</a>
            <a href="my_posts.php">Мои посты</a>
            <a href="logout.php">Выход</a>
        </nav>
    </header>

    <!-- Основной контейнер страницы -->
    <div class="container">
        <h2>Добавить новый пост</h2>
        <?php if ($message): ?>
            <p><?php echo htmlspecialchars($message); ?></p> <!-- Вывод сообщения об ошибке или успехе -->
        <?php endif; ?>

        <!-- Форма добавления поста -->
        <form class="post-form" method="POST" action="add_post.php" enctype="multipart/form-data">
            <div class="form-group">
                <label for="title">Заголовок:</label>
                <input type="text" id="title" name="title" required placeholder="Введите заголовок поста">
            </div>

            <div class="form-group">
                <label for="content">Содержание:</label>
                <textarea id="content" name="content" rows="5" required placeholder="Введите содержание поста"></textarea>
            </div>

            <div class="form-group">
                <label for="image">Изображение:</label>
                <input type="file" id="image" name="image" accept="image/*">
            </div>

            <button type="submit" class="submit-btn">Добавить пост</button>
        </form>
    </div>

    <!-- Подвал сайта -->
    <footer>
        <p>&copy; 2024 Travel Notes. Курсовая работа.</p>
    </footer>
</body>
</html>
