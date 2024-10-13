<?php
session_start();
include('db.php');

// Проверка роли администратора
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: auth.php");
    exit();
}

// Блокировка пользователя
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['block_user_id'])) {
    $block_user_id = (int)$_POST['block_user_id'];
    $stmt = $conn->prepare("UPDATE users SET blocked = 1 WHERE id = ?");
    $stmt->bind_param("i", $block_user_id);
    $stmt->execute();
    $message = "Пользователь заблокирован.";
}

// Удаление пользователя
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_user_id'])) {
    $delete_user_id = (int)$_POST['delete_user_id'];
    // Удаляем комментарии, связанные с пользователем
    $stmt = $conn->prepare("DELETE FROM comments WHERE user_id = ?");
    $stmt->bind_param("i", $delete_user_id);
    $stmt->execute();

    // Удаляем посты пользователя
    $stmt = $conn->prepare("DELETE FROM notes WHERE user_id = ?");
    $stmt->bind_param("i", $delete_user_id);
    $stmt->execute();

    // Удаляем пользователя
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param("i", $delete_user_id);
    $stmt->execute();
    $message = "Пользователь удален.";
}

// Получение списка пользователей
$stmt = $conn->prepare("SELECT id, username, blocked FROM users");
$stmt->execute();
$users = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Управление пользователями</title>
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
        <h2>Список пользователей</h2>
        <?php if (!empty($message)) echo "<p>$message</p>"; ?>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Имя пользователя</th>
                    <th>Статус</th>
                    <th>Действия</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($user = $users->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $user['id']; ?></td>
                    <td><?php echo htmlspecialchars($user['username']); ?></td>
                    <td><?php echo $user['blocked'] ? 'Заблокирован' : 'Активен'; ?></td>
                    <td>
                        <?php if (!$user['blocked']): ?>
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="block_user_id" value="<?php echo $user['id']; ?>">
                                <button type="submit">Заблокировать</button>
                            </form>
                        <?php endif; ?>
                        <form method="POST" style="display: inline;">
                            <input type="hidden" name="delete_user_id" value="<?php echo $user['id']; ?>">
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
