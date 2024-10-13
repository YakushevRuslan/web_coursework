<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start(); // Начало сессии
include('db.php'); // Подключение к базе данных

// Проверка подключения к базе данных
if ($mysqli->connect_error) {
    die("Ошибка подключения: " . $mysqli->connect_error);
}

// Получаем ID поста из URL
$post_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Проверяем, существует ли пост
$query = "SELECT notes.*, users.username AS author FROM notes JOIN users ON notes.user_id = users.id WHERE notes.id = ?";
$stmt = $mysqli->prepare($query);
$stmt->bind_param("i", $post_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "Пост не найден.";
    exit;
}

$post = $result->fetch_assoc();

// Получаем комментарии для данного поста
$comments_query = "SELECT comments.*, users.username AS commenter FROM comments JOIN users ON comments.user_id = users.id WHERE comments.note_id = ? ORDER BY comments.created_at DESC";
$comments_stmt = $mysqli->prepare($comments_query);
$comments_stmt->bind_param("i", $post_id);
$comments_stmt->execute();
$comments_result = $comments_stmt->get_result();


// Добавление нового комментария
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id']; // Предполагаем, что ID пользователя хранится в сессии
    $comment_content = trim($_POST['content']);
    error_log("Redirecting to view_post.php?id=" . $post_id);

    if (!empty($comment_content)) {
        $insert_comment_query = "INSERT INTO comments (note_id, user_id, content) VALUES (?, ?, ?)";
        $insert_stmt = $mysqli->prepare($insert_comment_query);
        $insert_stmt->bind_param("iis", $post_id, $user_id, $comment_content);
        $insert_stmt->execute();

        // Перезагружаем страницу, чтобы увидеть новый комментарий
        header("Location: view_notes.php?id=".$post_id);
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($post['title']); ?></title>
    <link rel="stylesheet" href="./css/styles.css">
</head>
<body>

    <header>
        <!-- Шапка сайта -->
        <h1>Travel Notes</h1>
        <nav>
            <a href="index.php">Главная</a>
            <a href="add_post.php">Создать пост</a>
            <a href="register.php">Регистрация</a>
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="logout.php">Выйти</a>
            <?php endif; ?>
        </nav>
    </header>

    <div class="content">
        <h2><?php echo htmlspecialchars($post['title']); ?></h2>
        <p><strong>Автор:</strong> <?php echo htmlspecialchars($post['author']); ?> | <strong>Дата:</strong> <?php echo htmlspecialchars($post['created_at']); ?></p>
        <img src="images/<?php echo htmlspecialchars($post['image']); ?>" alt="<?php echo htmlspecialchars($post['title']); ?>">
        <p><?php echo nl2br(htmlspecialchars($post['content'])); ?></p>
    </div>

    <div class="comments">
        <h2>Комментарии</h2>
        <?php if ($comments_result->num_rows > 0): ?>
            <?php while ($comment = $comments_result->fetch_assoc()): ?>
                <div class="comment">
                    <strong><?php echo htmlspecialchars($comment['commenter']); ?></strong> 
                    <p><?php echo nl2br(htmlspecialchars($comment['content'])); ?></p>
                    <small><?php echo htmlspecialchars($comment['created_at']); ?></small>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>Комментариев нет.</p>
        <?php endif; ?>

        <!-- Форма для добавления комментария -->
        <?php if (isset($_SESSION['user_id'])): ?>
            <div class="comment-form">
                <h3>Добавить комментарий</h3>
                <form action="view_notes.php?id=<?php echo $post_id; ?>" method="POST">
                    <textarea name="content" rows="4" placeholder="Ваш комментарий..."></textarea>
                    <button type="submit">Отправить</button>
                </form>
            </div>
        <?php else: ?>
            <p>Вы должны  <a href="auth.php">войти в систему</a>, чтобы оставлять комментарии.</p>
        <?php endif; ?>
    </div>

    <footer>
        <p>&copy; 2024 Travel Notes. Курсовая работа.</p>
    </footer>

</body>
</html>
