<?php
session_start();
if (!isset($_SESSION['username'])) {
    // Если пользователь не авторизован, перенаправить на страницу входа
    header('Location: auth.php');
    exit;
}

require 'db.php'; // Подключение к базе данных

// Получение ID поста из параметров запроса
$post_id = $_GET['id'];

// Проверка, что пост существует и принадлежит пользователю
$query = "SELECT * FROM notes WHERE id = :post_id AND user_id = :user_id";
$stmt = $pdo->prepare($query);
$stmt->bindParam(':post_id', $post_id, PDO::PARAM_INT);
$stmt->bindParam(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
$stmt->execute();
$post = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$post) {
    echo "Пост не найден или у вас нет прав для его редактирования.";
    exit;
}

$message = ""; // Сообщение для отображения ошибок или успеха

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $_POST['title'];
    $content = $_POST['content'];

    // Обработка загрузки изображения
    $image = $post['image']; // Сохраняем текущее изображение
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $image = basename($_FILES['image']['name']);
        $target_dir = "images/";
        if (!move_uploaded_file($_FILES['image']['tmp_name'], $target_dir . $image)) {
            $message = "Ошибка при загрузке изображения.";
        }
    }

    // Обновление поста в базе данных
    $updateQuery = "UPDATE notes SET title = :title, content = :content, image = :image WHERE id = :post_id";
    $updateStmt = $pdo->prepare($updateQuery);
    $updateStmt->bindParam(':title', $title);
    $updateStmt->bindParam(':content', $content);
    $updateStmt->bindParam(':image', $image);
    $updateStmt->bindParam(':post_id', $post_id, PDO::PARAM_INT);

    if ($updateStmt->execute()) {
        $message = "Пост успешно обновлён.";
        header("Location: my_posts.php"); // Перенаправление на страницу с постами
        exit();
    } else {
        $message = "Ошибка при обновлении поста: " . $updateStmt->errorInfo()[2];
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Редактировать пост</title>
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
            <a href="edit_profile.php">Редактировать профиль</a>
            <a href="logout.php">Выйти</a>
        </nav>
    </header>

    <!-- Основной контейнер страницы -->
    <div class="container">
        <h2>Редактировать пост</h2>
        <?php if ($message): ?>
            <p><?php echo htmlspecialchars($message); ?></p> <!-- Вывод сообщения об ошибке или успехе -->
        <?php endif; ?>

        <!-- Форма редактирования поста -->
        <form class="post-form" method="POST" action="edit_post.php?id=<?php echo $post_id; ?>" enctype="multipart/form-data">
            <div class="form-group">
                <label for="title">Заголовок:</label>
                <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($post['title']); ?>" required placeholder="Введите заголовок поста">
            </div>

            <div class="form-group">
                <label for="content">Содержание:</label>
                <textarea id="content" name="content" rows="5" required placeholder="Введите содержание поста"><?php echo htmlspecialchars($post['content']); ?></textarea>
            </div>

            <div class="form-group">
                <label for="image">Изображение:</label>
                <input type="file" id="image" name="image" accept="image/*">
                <?php if ($post['image']): ?>
                    <p>Текущее изображение: <img src="images/<?php echo htmlspecialchars($post['image']); ?>" alt="Текущее изображение" style="max-width: 200px;"/></p>
                <?php endif; ?>
            </div>

            <button type="submit" class="submit-btn">Обновить пост</button>
        </form>
    </div>

    <!-- Подвал сайта -->
    <footer>
        <p>&copy; 2024 Travel Notes. Курсовая работа.</p>
    </footer>
</body>
</html>
