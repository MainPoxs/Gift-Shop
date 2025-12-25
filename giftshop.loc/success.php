<?php
include 'config.php';
$order_id = $_GET['order_id'] ?? null;
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/css/style_success.css">
    <title>Заказ оплачен</title>
</head>

<body>
    <div class="container">
        <h1>&#9989; Заказ #<?= htmlspecialchars($order_id) ?> успешно оплачен!</h1>
        <p>Спасибо за покупку в нашем магазине подарков ручной работы!</p>
        <a class="btn-success" href=" index.php">Вернуться в магазин</a>
    </div>
</body>

</html>