<?php
session_start();
include('db.php');

// Проверка авторизации
if (!isset($_SESSION['username'])) {
    header("Location: auth.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $note_id = $_POST['note_id'];
    $content = $_POST['content'];
    $user_id = $_SESSION['user_id']; // ID авторизованного пользователя

    // Сохранение комментария в базе данных
    $stmt = $mysqli->prepare("INSERT INTO comments (note_id, user_id, content) VALUES (?, ?, ?)");
    $stmt->bind_param("iis", $note_id, $user_id, $content);
    $stmt->execute();

    header("Location: view_notes.php");
    exit();
}
?>
22






























































































 


























