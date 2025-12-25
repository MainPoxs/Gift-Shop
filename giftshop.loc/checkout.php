<?php
include 'config.php';
session_start();

// Проверка авторизации
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}
$user_id = intval($_SESSION['user_id']);

// Получаем данные пользователя
$user_query = mysqli_query(
    $connect,
    "SELECT firstname, email FROM users WHERE id = $user_id;"
);
$user = mysqli_fetch_assoc($user_query);

// Получаем товары из корзины
$cart_query = "SELECT c.quantity, p.id AS product_id,
    p.name, p.image, p.price 
    FROM carts AS c
    JOIN products AS p ON c.product_id = p.id
    WHERE c.user_id = $user_id;";

$result = mysqli_query($connect, $cart_query);

//Итог
$total = 0;
$cart_items = [];

while ($item = mysqli_fetch_assoc($result)) {
    $cart_items[] = $item;
    $total += $item['price'] * $item['quantity'];
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/css/style_order.css">
    <title>Оформление заказа</title>
</head>

<body>
    <div class="container">
        <h1>Оформление заказа</h1>
        <a href="cart.php" class="btn-back">
            &larr; В корзину </a>

        <form class="orderForm" method="POST" action="procedure_order.php">
            <details open>
                <summary><strong>Данные покупателя</strong></summary>

                <label for="recipient_name">ФИО получателя *</label><br>
                <input id="recipient_name" type="text" name="recipient_name"
                    value="<?= htmlspecialchars($user['firstname']) ?>" required><br><br>

                <label for="phone">Телефон *</label><br>
                <input id="phone" type="tel" name="phone" required><br><br>

                <label for="recipient_email">Email получателя</label><br>
                <input id="recipient_email" type="email" name="recipient_email"
                    value="<?= htmlspecialchars($user['email']) ?>"><br><br>

                <label for="shipping_address">Адрес доставки *</label><br>
                <textarea id="shipping_address" name="shipping_address" rows="3" required>
                    </textarea><br><br>
            </details>

            <details open>
                <summary><strong>Детали заказа</strong></summary>
                <table>
                    <thead>
                        <tr>
                            <th>Товар</th>
                            <th>Кол-во</th>
                            <th>Цена</th>
                            <th>Итого</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php foreach ($cart_items as $item): ?>
                            <tr>
                                <td><?= htmlspecialchars($item['name']) ?></td>
                                <td><?= $item['quantity'] ?></td>
                                <td><?= number_format($item['price'], 2, ',', ' ') ?>
                                    &#8381;</td>
                                <td><?= number_format($item['price'] * $item['quantity'], 2, ',', ' ') ?>
                                    &#8381;</td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>

                    <tfoot>
                        <tr>
                            <td colspan="3"><strong>Итого:</strong></td>
                            <td><strong><?= number_format($total, 2, ',', ' ') ?>
                                    &#8381;</strong></td>
                        </tr>
                    </tfoot>
                </table>
                <input type="hidden" name="total_amount" value="<?= $total ?>">
            </details>

            <details open>
                <summary><strong>Способ оплаты</strong></summary>
                <p>Оплата производится банковской картой на защищённой странице.</p>
                <p><strong>Общая сумма к оплате:
                        <?= number_format($total, 2, ',', ' ') ?>
                        &#8381;
                    </strong>
                </p>
                <p>Дата покупки: <?= date('d.m.Y') ?> </p>
            </details>

            <div class="confirmation">
                <label>
                    <input type="checkbox" name="checkbox" required />
                    Я подтверждаю правильность указанных данных и согласен(на) с условиями
                </label>
                <button type="submit" class="btn-pay">Перейти к оплате</button>
            </div>
        </form>
    </div>
</body>

</html>