<?php include 'config.php';
session_start();

// Инициализация переменных
$username = '';
$password = '';
$error = '';

// Обработка формы входа
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Получение данных из формы
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    // Ищем пользователя в базе данных
    $users = mysqli_query(
        $connect,
        "SELECT u.id, u.firstname, u.username,
         u.password_hash, r.name AS `role` 
         FROM users AS u 
         JOIN roles AS r 
         ON u.role_id = r.id 
         WHERE u.username = '$username';"
    );
    $user = mysqli_fetch_assoc($users);

    // Проверка существования пользователя и 
    // проверка пароля с хэшированным паролем
    if (
        isset($user) &&
        password_verify($password, $user["password_hash"])
    ) {
        // Сохраняем данные пользователя в сессии
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['firstname'] = $user['firstname'];
        $_SESSION['username'] = $username;
        $_SESSION['role'] = $user['role'];

        // Перенаправляем пользователя на главную страницу index.php
        header('Location: index.php');
        exit();
    } else {
        // Если авторизация не удалась, показываем ошибку
        $error = 'Неверное имя пользователя или пароль';
    }
}
?>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/css/style_reg.css">
    <title>Вход</title>
</head>

<body>
    <div class="container">
        <h2>Вход в систему</h2>
        <div class="error-block">
            <?php if (isset($error))
                echo "<p class='error'>$error</p>"; ?>
        </div>
        <form method="POST">
            <label for="username">Логин<br>
                <input type="text" name="username" placeholder="Логин" required>
            </label><br><br>
            <label for="password">Пароль<br>
                <input type="password" name="password" placeholder="Пароль" required>
            </label><br><br>
            <button type="submit">Войти</button>
        </form>
        <div class="login-link">Нет аккаунта? <a href="register.php">Зарегистрироваться</a></div>
        <a href="index.php" class="back-link">&larr; На главную</a>
    </div>
</body>

</html>