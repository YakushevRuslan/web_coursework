<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
include('db.php');

// Проверка роли администратора
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: auth.php");
    exit();
}

// Удаление поста
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_post_id'])) {
    $delete_post_id = (int)$_POST['delete_post_id'];
    
    // Удаляем комментарии, связанные с постом
    $stmt = $conn->prepare("DELETE FROM comments WHERE note_id = ?");
    $stmt->bind_param("i", $delete_post_id);
    $stmt->execute();

    // Удаляем сам пост
    $stmt = $conn->prepare("DELETE FROM notes WHERE id = ?");
    $stmt->bind_param("i", $delete_post_id);
    $stmt->execute();
    $message = "Пост удален.";
}

// Обновление поста
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_post_id'])) {
    $update_post_id = (int)$_POST['update_post_id'];
    $updated_content = $_POST['content'];

    // Обновляем пост
    $stmt = $conn->prepare("UPDATE notes SET content = ? WHERE id = ?");
    $stmt->bind_param("si", $updated_content, $update_post_id);
    $stmt->execute();
    $message = "Пост обновлен.";
}

// Получение списка постов с именами авторов
$stmt = $conn->prepare("
    SELECT n.id, n.title, n.content, n.user_id, u.username 
    FROM notes n 
    JOIN users u ON n.user_id = u.id
");
$stmt->execute();
$posts = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Управление постами</title>
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
        <h2>Список постов</h2>
        <?php if (!empty($message)) echo "<p>$message</p>"; ?>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Заголовок</th>
                    <th>Содержимое</th>
                    <th>Автор</th>
                    <th>Действия</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($post = $posts->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $post['id']; ?></td>
                    <td><?php echo htmlspecialchars($post['title']); ?></td>
                    <td>
                        <form method="POST" style="display: inline;">
                            <textarea name="content" rows="8" cols="50"><?php echo htmlspecialchars($post['content']); ?></textarea>
                            <input type="hidden" name="update_post_id" value="<?php echo $post['id']; ?>">
                            <button type="submit">Сохранить</button>
                        </form>
                    </td>
                    <td><?php echo $post['user_id'] . " (" . htmlspecialchars($post['username']) . ")"; ?></td>
                    <td>
                        <form method="POST" style="display: inline;">
                            <input type="hidden" name="delete_post_id" value="<?php echo $post['id']; ?>">
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
