<?php include 'config.php';
session_start();

if (
    $_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['id'])
) {
    $product_id = intval($_GET['id']);

    $query = "SELECT * FROM products WHERE id = $product_id";
    $result = mysqli_query($connect, $query);
}

// Добавление товара в корзину
if (
    $_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['action']) &&
    $_GET['action'] == 'add' && isset($_GET['id'])
) {
    $user_id = intval($_SESSION['user_id']);
    $product_id = intval($_GET['id']);

    //Проверяем наличие товара в БД
    $query = "SELECT * FROM products WHERE id = $product_id AND stock_quantity > 0;";
    $result = mysqli_query($connect, $query);

    //Извлекаем строку ввиде ассоциативного массива
    $product = mysqli_fetch_assoc($result);

    if ($product) {
        // Проверяем, есть ли уже такой товар в корзине пользователя
        $check_query = "SELECT id, quantity FROM carts
         WHERE user_id = $user_id AND product_id = $product_id";

        $check_sql = mysqli_query($connect, $check_query);
        $existing_item = mysqli_fetch_assoc($check_sql);

        //Если товар уже в корзине — увеличиваем количество 
        if ($existing_item) {
            $new_quantity = $existing_item['quantity'] + 1;

            $cart_id = $existing_item['id'];
            $update_query = "UPDATE carts SET quantity = $new_quantity
             WHERE id = $cart_id";

            mysqli_query($connect, $update_query);
        } else {
            //Сохранение в базу данных             
            $sql = "INSERT INTO carts (user_id, product_id, quantity)            
            VALUES ($user_id, $product_id, 1);";

            mysqli_query($connect, $sql);
        }
        mysqli_query(
            $connect,
            "UPDATE products SET stock_quantity = stock_quantity-1
                WHERE id = $product_id;"
        );
        // Перенаправление — избегаем повторного добавления
        header('Location: cart.php');
        exit();
    }
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/css/style_details.css">
    <title>О товаре</title>
</head>

<body>
    <div class="container">
        <?php if ($result && mysqli_num_rows($result) > 0):
            $product = mysqli_fetch_assoc($result) ?>

            <div class="product-header">
                <div class="product-image">
                    <img class="div-img" src="<?= $product['image'] ?>" alt="картинка">
                </div>
                <div class="product-info">
                    <h1><?= htmlspecialchars($product['name']) ?></h1>
                    <div class="price-and-button">
                        <span class="price">&#128184;
                            <?= number_format($product['price'], 2, '.', ' ') ?>
                            &#8381;
                        </span>
                        <a href="?action=add&id=<?= intval($product['id']) ?>" class="btn-add-to-cart">
                            Добавить в корзину
                        </a>
                    </div>
                </div>
            </div>

            <div class="product-description">
                <?= $product['description'] ?>
            </div>

        <?php endif; ?>
        <a href="index.php" class="btn-back">
            &larr; Вернуться в магазин </a>
    </div>
</body>

</html>