<!-- Конфигурация для соединения с базой данных -->
<?php
$host = 'localhost';
$login = 'root';
$password = '';
$db_name = 'gift_shop';

$connect = mysqli_connect($host, $login, $password, $db_name);
if (!$connect) {
    die("Ошибка соединения: " . mysqli_connect_error());
}
?>