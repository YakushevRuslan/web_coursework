<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
include('db.php');

// Логирование текущих значений сессии
file_put_contents('log.txt', 'user_id: ' . (isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'не установлен') . PHP_EOL, FILE_APPEND);
file_put_contents('log.txt', 'role: ' . (isset($_SESSION['role']) ? $_SESSION['role'] : 'не установлен') . PHP_EOL, FILE_APPEND);

// Проверка роли администратора
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    file_put_contents('log.txt', 'Доступ запрещен, перенаправление на auth.php' . PHP_EOL, FILE_APPEND);
    header("Location: auth.php"); // Перенаправление, если не администратор
    exit();
}
file_put_contents('log.txt', 'Проверка доступа на ' . date('Y-m-d H:i:s') . PHP_EOL, FILE_APPEND);
// Ваш остальной код здесь...


// Блокировка пользователя
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['block_user_id'])) {
    $block_user_id = (int)$_POST['block_user_id'];
    $stmt = $conn->prepare("UPDATE users SET blocked = 1 WHERE id = ?");
    $stmt->bind_param("i", $block_user_id);
    if ($stmt->execute()) {
        $message = "Пользователь заблокирован.";
    } else {
        $message = "Ошибка блокировки пользователя.";
    }
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
    if ($stmt->execute()) {
        $message = "Пользователь удален.";
    } else {
        $message = "Ошибка удаления пользователя.";
    }
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
    <link rel="stylesheet" href="./css/admin_styles.css">
</head>
<body>
    <header>
        <h1>Панель администратора</h1>
        <nav>
            <a href="admin.php">Управление пользователями</a>
            <a href="manage_posts.php">Управление постами</a>
            <a href="manage_comments.php">Управление комментариями</a>
            <a href="logout.php">Выйти</a>
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
