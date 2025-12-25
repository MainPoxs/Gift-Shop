<?php include 'config.php';
session_start();

// Очистка всех данных сессии
$_SESSION = [];

// Уничтожение сессии
session_destroy();

// Перенаправление на страницу входа
header('Location: login.php');
exit;
?>