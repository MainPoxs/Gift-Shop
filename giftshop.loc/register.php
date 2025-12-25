<?php
include 'config.php';

// Инициализация переменных
$firstname = '';
$username = '';
$email = '';
$password = '';

// Обработка формы входа
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Получение данных из формы
    $firstname = trim($_POST['firstname'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    // Валидация
    if (
        empty($username) || empty($username) ||
        empty($email) || empty($password)
    ) {
        echo 'Все поля обязательны.';
    } elseif (strlen($password) < 6) {
        echo 'Пароль должен быть не менее 6 символов.';
    } else {

        // Хэшируем пароль
        $hashedPassword = password_hash(
            $password,
            PASSWORD_DEFAULT
        );

        $sql = "INSERT INTO users (firstname, username, email, password_hash)
         VALUES ('$firstname', '$username', '$email', '$hashedPassword')";

        if (mysqli_query($connect, $sql)) {
            header('Location: login.php');
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/css/style_reg.css">
    <title>Регистрация</title>
</head>

<body>
    <div class="container">
        <h2>Регистрация</h2>
        <form method="POST">
            <label for="firstname">Имя пользователя<br>
                <input id="firstname" type="text" name="firstname" required placeholder="Иван">
            </label><br><br>
            <label for="username">логин<br>
                <input id="username" type="text" name="username" required minlength="3" maxlength="50"
                    placeholder="ivan">
            </label><br><br>
            <label for="email">Email<br>
                <input id="email" type="email" name="email" required placeholder="you@example.com">
            </label><br><br>
            <label for="password">Пароль<br>
                <input id="password" type="password" name="password" required minlength="6" placeholder="....">
            </label><br><br>
            <button type="submit" class="btn">Зарегистрироваться</button>
        </form>
        <div class="login-link">Уже есть аккаунт? <a href="login.php">Войти</a></div>
        <a href="index.php" class="back-link">&larr; На главную</a>
    </div>
</body>

</html>