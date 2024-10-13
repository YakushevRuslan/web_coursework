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

// Обработка удаления поста
if (isset($_POST['delete'])) {
    $post_id = $_POST['post_id'];
    try {
        $deleteQuery = "DELETE FROM notes WHERE id = :post_id AND user_id = :user_id";
        $deleteStmt = $pdo->prepare($deleteQuery);
        $deleteStmt->bindParam(':post_id', $post_id, PDO::PARAM_INT);
        $deleteStmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $deleteStmt->execute();
    } catch (PDOException $e) {
        echo "Ошибка удаления поста: " . $e->getMessage();
    }
}

// Запрос на выбор всех постов, принадлежащих авторизованному пользователю
try {
    $query = "SELECT * FROM notes WHERE user_id = :user_id";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    
    $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Ошибка запроса: " . $e->getMessage();
    exit;
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Мои посты</title>
    <link rel="stylesheet" href="./css/styles.css"> <!-- Подключение основного CSS файла -->
</head>
<body>
    <!-- Шапка сайта -->
    <header>
        <h1>Travel Notes. Мой блог</h1>
        <nav>
            <a href="index.php">Главная</a>
            <a href="add_post.php">Создать пост</a>
            <a href="my_posts.php">Мои посты</a>
            <a href="edit_profile.php">Редактировать профиль</a>
            <a href="logout.php">Выйти</a>
        </nav>
    </header>

    <!-- Основной контейнер -->
    <div class="container">
        <h2>Мои посты</h2>
        <a href="add_post.php" class="btn">Создать новый пост</a> <!-- Кнопка для добавления нового поста -->
        <?php if (count($posts) > 0): ?>
            <ul>
                <?php foreach ($posts as $post): ?>
                    <li class="post">
                        <a href="view_post.php?id=<?php echo $post['id']; ?>"><?php echo htmlspecialchars($post['title']); ?></a>
                        <span>(<?php echo htmlspecialchars($post['created_at']); ?>)</span>
                        
                        <!-- Кнопки редактирования и удаления поста -->
                        <form method="GET" action="edit_post.php" style="display:inline;">
                            <input type="hidden" name="id" value="<?php echo $post['id']; ?>">
                            <button type="submit" class="btn">Редактировать</button>
                        </form>
                        
                        <form method="POST" action="" style="display:inline;">
                            <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
                            <button type="submit" name="delete" onclick="return confirm('Вы уверены, что хотите удалить этот пост?');" class="btn">Удалить</button>
                        </form>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p>У вас нет опубликованных постов.</p>
        <?php endif; ?>
    </div>

    <!-- Футер сайта -->
    <footer>
        <p>&copy; 2024 Travel Notes. Курсовая работа. </p>
    </footer>
</body>
</html>
