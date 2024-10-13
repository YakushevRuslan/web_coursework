<?php

try {
    $dsn = "mysql:host=localhost;dbname=travel_notes"; // DSN строка
    $username = "admin"; // Имя пользователя
    $password = "0101"; // Пароль
    $conn = new mysqli('localhost', 'admin', '0101', 'travel_notes');
    $mysqli = new mysqli("localhost", "admin", "0101", "travel_notes");
    // Попытка подключения
    $pdo = new PDO($dsn, $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    //echo "Подключение установлено успешно!"; // Успешное подключение
} catch (PDOException $e) {
    // Вывод подробной информации об ошибке
    echo "Ошибка подключения: " . $e->getMessage();
    exit();
}
?>

