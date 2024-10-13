<?php
session_start();
include('db.php');

// Проверка роли администратора
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: auth.php");
    exit();
}

// Удаление комментария
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_comment_id'])) {
    $delete_comment_id = (int)$_POST['delete_comment_id'];
    $stmt = $conn->prepare("DELETE FROM comments WHERE id = ?");
    $stmt->bind_param("i", $delete_comment_id);
    $stmt->execute();
    $message = "Комментарий удален.";
}

// Редактирование комментария
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_comment_id'])) {
    $edit_comment_id = (int)$_POST['edit_comment_id'];
    $new_content = $_POST['new_content'];

    $stmt = $conn->prepare("UPDATE comments SET content = ? WHERE id = ?");
    $stmt->bind_param("si", $new_content, $edit_comment_id);
    $stmt->execute();
    $message = "Комментарий отредактирован.";
}

// Получение списка комментариев с именами пользователей и заголовками постов
$stmt = $conn->prepare("
    SELECT c.id, c.content, c.note_id, c.user_id, u.username, n.title 
    FROM comments c 
    JOIN users u ON c.user_id = u.id 
    JOIN notes n ON c.note_id = n.id
");
$stmt->execute();
$comments = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Управление комментариями</title>
    <link rel="stylesheet" href="css/admin_styles.css"> <!-- Стили администратора -->
</head>
<body>
    <header>
        <h1>Панель администратора</h1>
        <nav>
            <a href="admin.php">Назад в панель</a>
        </nav>
    </header>

    <div class="admin-panel">
        <h2>Список комментариев</h2>
        <?php if (!empty($message)) echo "<p>$message</p>"; ?>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Комментарий</th>
                    <th>Автор</th>
                    <th>Пост</th>
                    <th>Действия</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($comment = $comments->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $comment['id']; ?></td>
                    <td>
                        <form method="POST" style="display: inline;">
                            <input type="hidden" name="edit_comment_id" value="<?php echo $comment['id']; ?>">
                            <input type="text" name="new_content" value="<?php echo htmlspecialchars($comment['content']); ?>" required>
                            <button type="submit">Сохранить</button>
                        </form>
                    </td>
                    <td><?php echo htmlspecialchars($comment['username']); ?></td>
                    <td><?php echo htmlspecialchars($comment['title']); ?></td>
                    <td>
                        <form method="POST" style="display: inline;">
                            <input type="hidden" name="delete_comment_id" value="<?php echo $comment['id']; ?>">
                            <button type="submit" onclick="return confirm('Вы уверены?')">Удалить</button>
                        </form>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <footer>
        <p>&copy; 2024 Travel Notes. Панель администратора.</p>
    </footer>
</body>
</html>
