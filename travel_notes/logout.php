<?php
session_start(); // Начинаем сессию

// Удаляем все данные сессии
$_SESSION = array(); 

// Уничтожаем сессию
session_destroy(); 

// Перенаправляем пользователя на главную страницу 
header("Location: index.php");
exit();
?>
