<?php include 'config.php';
session_start();

// Проверка авторизации
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = intval($_SESSION['user_id']);

// Обработка обновления количества
if (
    $_SERVER['REQUEST_METHOD'] == 'POST' &&
    isset($_POST['action'])
) {
    $cart_id = intval($_POST['id'] ?? 0);

    //Получаем текущие данные товара в корзине
    $current = mysqli_fetch_assoc(
        mysqli_query(
            $connect,
            "SELECT c.quantity, c.product_id, p.stock_quantity
             FROM carts AS c JOIN products AS p
              ON c.product_id = p.id 
              WHERE c.id = $cart_id AND c.user_id = $user_id;"
        )
    );

    if (!$current) {
        header("Location: cart.php");
        exit();
    }

    $product_id = $current['product_id'];
    $old_qty = $current['quantity'];
    $stock = $current['stock_quantity'];

    if ($_POST['action'] == 'prod-del') {
        if ($old_qty > 1) {
            // Уменьшаем количество в корзине            
            mysqli_query(
                $connect,
                "UPDATE carts SET quantity = quantity - 1
                       WHERE id = $cart_id;"
            );

            //Возвращаем 1 шт на склад
            mysqli_query(
                $connect,
                "UPDATE products SET stock_quantity = stock_quantity + 1 
                WHERE id = $product_id;"
            );
        } else {
            // Удаляем запись
            mysqli_query(
                $connect,
                "DELETE FROM carts WHERE id = $cart_id;"
            );
            //Возвращаем все количество
            mysqli_query(
                $connect,
                "UPDATE products SET stock_quantity = stock_quantity + $old_qty
              WHERE id = $product_id;"
            );
        }
    } elseif ($_POST['action'] == 'prod-add') {
        // Проверяем, есть ли товар на складе
        if ($stock > 0) {
            // Увеличиваем количество в корзине
            mysqli_query(
                $connect,
                "UPDATE carts SET quantity = quantity + 1
              WHERE id = $cart_id;"
            );
            //Списываем 1 шт со склада
            mysqli_query(
                $connect,
                "UPDATE products SET stock_quantity = stock_quantity - 1
          WHERE id = $product_id;"
            );
        }
        header("Location: cart.php");
        exit();
    }
}

// Очистка корзины
if (
    $_SERVER["REQUEST_METHOD"] == "GET" &&
    isset($_GET['action']) && $_GET['action'] == 'clear'
) {
    //Получаем все товары из корзины пользователя с количеством
    $cart_items = mysqli_query($connect, "
        SELECT product_id, quantity 
        FROM carts 
        WHERE user_id = $user_id;");

    //Восстанавливаем остатки на складе
    if ($cart_items && mysqli_num_rows($cart_items) > 0) {
        while ($item = mysqli_fetch_assoc($cart_items)) {
            $product_id = intval($item['product_id']);
            $quantity = intval($item['quantity']);

            // Увеличиваем количество на складе
            mysqli_query(
                $connect,
                "UPDATE products 
             SET stock_quantity = stock_quantity + $quantity 
             WHERE id = $product_id;"
            );
        }
    }

    //Удаляем всю корзину
    mysqli_query(
        $connect,
        "DELETE FROM carts WHERE user_id = $user_id;"
    );
    header("Location: cart.php");
    exit();
}

// Удаление одного товара
if (isset($_GET['remove'])) {
    $cart_id = intval($_GET['remove']);

    //Получаем product_id и quantity
    $item = mysqli_fetch_assoc(
        mysqli_query(
            $connect,
            "SELECT product_id, quantity 
          FROM carts
          WHERE id = $cart_id AND user_id = $user_id;"
        )
    );

    if ($item) {
        //Возвращаем товар на склад
        $product_id = intval($item['product_id']);
        $quantity = intval($item['quantity']);
        mysqli_query($connect, "
            UPDATE products 
            SET stock_quantity = stock_quantity + $quantity 
            WHERE id = $product_id;");

        // Удаляем из корзины
        mysqli_query(
            $connect,
            "DELETE FROM carts 
            WHERE id = $cart_id AND user_id = $user_id;"
        );
    }
    header("Location: cart.php");
    exit();
}

// Отображение корзины
$cart_query = "SELECT c.id AS cart_id, c.quantity, p.id AS product_id,
    p.name, p.image, p.price, p.stock_quantity 
    FROM carts AS c
    JOIN products AS p ON c.product_id = p.id
    WHERE c.user_id = $user_id;";

$result = mysqli_query($connect, $cart_query);
$total = 0;

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/css/style_cart.css">
    <title>Корзина</title>
</head>

<body>
    <!-- Отображение корзины -->
    <div class="container">
        <h1>Ваша корзина</h1>
        <!--Проверка наличия товаров в базе данных-->
        <?php if (mysqli_num_rows($result) <= 0): ?>
        <div class="cart-empty">
            <img src="/photo/cart.png" alt="картинка">
            <p>Ваша корзина пуста.</p>
            <a href="index.php" class="btn-back">
                &larr; Вернуться в магазин </a>
        </div>

        <?php else: ?>
        <div class="cart-full">
            <?php if (mysqli_num_rows($result) > 0): ?>
            <?php while ($item = mysqli_fetch_assoc($result)):
                        $sum = $item['price'] * $item['quantity'];
                        $total += $sum;
                        $cart_id = intval($item['cart_id']);
                        $qty = intval($item['quantity']);
                        $product_id = intval($item['product_id']);
                        $stock_qty = intval($item['stock_quantity']); ?>

            <div class="product-item">
                <div class="product-item_img">
                    <img src="<?= htmlspecialchars($item['image']) ?>" alt="картинка">
                </div>
                <div class="product-text">
                    <h4><?= htmlspecialchars($item['name']) ?></h4>
                    <p>&#128184;
                        <?= number_format($item['price'], 2, ',', ' ') ?>
                        &#8381;
                    </p>
                    <p>Итого: <?= number_format($sum, 2, ',', ' ') ?> &#8381;</p>

                    <!-- Форма управления количеством -->
                    <form method="POST">
                        <input type="hidden" name="id" value="<?= $cart_id ?>">
                        <input type="hidden" name="product_id" value="<?= $product_id ?>">

                        <button type="submit" name="action" value="prod-del" class="btn-del">&#10134;</button>
                        <span class="quantity-display"><?= $qty ?></span>

                        <?php if ($item['stock_quantity'] > 0): ?>
                        <button type="submit" name="action" value="prod-add" class="btn-add">&#10133;</button>
                        <?php endif; ?>

                    </form>
                    <!-- Отдельная ссылка на удаление -->
                    <a href="?remove=<?= $cart_id ?>" class="btn-remove">
                        Удалить
                    </a>
                </div>
            </div>
            <?php endwhile; ?>
            <?php endif; ?>
            <div class="cart-summary">
                <h2>Итого: <?= number_format($total, 2, ',', ' ') ?> &#8381;</h2>
                <a href="index.php" class="btn">Выбрать ещё товары</a>
                <a href="checkout.php" class="btn">К оформлению</a>
            </div>
            <form method="GET">
                <button type="submit" name="action" value="clear" class="btn-clear">
                    Очистить корзину
                </button>
            </form>
        </div>
        <?php endif; ?>
    </div>
</body>

</html>