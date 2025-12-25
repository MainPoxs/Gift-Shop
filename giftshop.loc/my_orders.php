<?php
include 'config.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = intval($_SESSION['user_id']);

// Получаем заказы и связанные товары
$query = "SELECT o.id AS order_id, 
    o.total_amount, 
    o.created_at, 
    o.is_paid,
    oi.quantity,
    p.id AS product_id,          
    p.name AS product_name,
    p.image AS product_image 
FROM orders AS o 
JOIN order_items AS oi ON o.id = oi.order_id
JOIN products AS p ON oi.product_id = p.id
WHERE o.user_id = $user_id
ORDER BY o.created_at DESC;";

$result = mysqli_query($connect, $query);
if (!$result) {
    die("Ошибка базы данных: " . mysqli_error($connect));
}

// Группируем товары по заказам
$orders = [];
while ($row = mysqli_fetch_assoc($result)) {
    $oid = $row['order_id'];
    if (!isset($orders[$oid])) {
        $orders[$oid] = [
            'id' => $oid,
            'total_amount' => $row['total_amount'],
            'created_at' => $row['created_at'],
            'is_paid' => $row['is_paid'],
            'items' => []
        ];
    }
    $orders[$oid]['items'][] = [
        'id' => $row['product_id'],
        'name' => $row['product_name'],
        'image' => $row['product_image'],
        'quantity' => $row['quantity']
    ];
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/css/style_myorders.css">
    <title>Мои покупки</title>
</head>

<body>
    <div class="container">
        <h1>Мои покупки</h1>

        <?php if (empty($orders)): ?>
            <div class="no-orders">
                У вас пока нет заказов.<br>
                Посетите <a href="index.php">магазин</a>, чтобы что-нибудь купить!
            </div>
        <?php else: ?>
            <?php foreach ($orders as $order): ?>
                <div class="order-item">
                    <div class="order-header">
                        <span>Заказ #<?= htmlspecialchars($order['id']) ?></span>
                        <?php if ($order['is_paid']): ?>
                            <span class="order-status status-paid">Оплачен</span>
                        <?php else: ?>
                            <span class="order-status status-pending">Не оплачен</span>
                        <?php endif; ?>
                    </div>
                    <div class="order-date">
                        <?= date('d.m.Y в H:i', strtotime($order['created_at'])) ?>
                    </div>

                    <!-- Товары в заказе -->
                    <?php foreach ($order['items'] as $item): ?>
                        <div class="product-in-order">
                            <a class="link_detail" href="details_product.php?id=<?= intval($item['id']) ?>">
                                <img src="<?= htmlspecialchars($item['image']) ?>" alt="картинка" class="product-image">
                            </a>
                            <div class="product-info">
                                <div class="product-name"><?= htmlspecialchars($item['name']) ?></div>
                                <div class="product-qty">Кол-во: <?= htmlspecialchars($item['quantity']) ?></div>
                            </div>
                        </div>
                    <?php endforeach; ?>

                    <div class="order-total">
                        Итого: <?= number_format($order['total_amount'], 2, ',', ' ') ?>
                        &#8381;
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>

        <a href="index.php" class="back-link">← Вернуться в магазин</a>
    </div>
</body>

</html>