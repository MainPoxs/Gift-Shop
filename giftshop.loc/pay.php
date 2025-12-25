<?php include 'config.php';
$order_id = intval($_GET['order_id']) ?? null;

if (!$order_id) {
    die("Ошибка: неверный номер заказа.");
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/css/style_pay.css">
    <title>Оплата заказа</title>
</head>

<body>
    <div class="container">
        <h1>Оплата заказа</h1>

        <form method="POST" action="order_success.php" class="card-form">
            <input type="hidden" name="order_id" value="<?= $order_id ?>">
            <h3>Данные банковской карты</h3>

            <div class="card-icon">
                <input type="text" class="card-input" placeholder="Номер карты (16 цифр)" maxlength="16" required>
            </div>

            <div class="card-row">
                <input type="text" class="card-input" name="card_expiry" placeholder="ММ/ГГ" maxlength="5" required>
                <input type="text" class="card-input" name="card_cvv" placeholder="CVV" maxlength="3" required>
            </div>

            <input type="text" class="card-input" name="card_name" placeholder="Имя и фамилия на карте" required>

            <p>
                &#128274; Ваши данные защищены.
            </p>

            <button type="submit" class="btn-pay">Оплатить</button>
        </form>

        <div>
            <a href="index.php" class="back-link">← Вернуться в магазин</a>
        </div>
    </div>

</body>

</html>