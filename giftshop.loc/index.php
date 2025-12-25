<?php include 'config.php';
session_start();

// Добавление товара в корзину
if (
    $_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['action']) &&
    $_GET['action'] == 'add' && isset($_GET['id'])
) {
    // Неавторизованный пользователь — перенаправить на логин
    if (!isset($_SESSION['user_id'])) {
        header('Location: login.php');
        exit();
    }

    $user_id = intval($_SESSION['user_id']);
    $product_id = intval($_GET['id']);
    $role = $_SESSION['role'];

    //Проверяем наличие товара в БД
    $query = "SELECT * FROM products WHERE id = $product_id AND stock_quantity > 0;";
    $result = mysqli_query($connect, $query);


    if (!$result) {
        // обработка ошибки
        error_log("Ошибка: " . mysqli_error($connect));
    }

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
        //Уменьшаем в базе данных
        mysqli_query(
            $connect,
            "UPDATE products SET stock_quantity = stock_quantity-1
                WHERE id = $product_id;"
        );

        // Перенаправление, чтобы избежать повторного добавления
        header('Location: index.php');
        exit();
    }
}

// Подсчёт общего количества товаров в корзине
$count_all = 0;
if (isset($_SESSION['user_id'])) {
    $user_id = intval($_SESSION['user_id']);
    $cart_count = "SELECT SUM(quantity) AS total FROM carts WHERE user_id = $user_id";

    $res = mysqli_query($connect, $cart_count);
    $row = mysqli_fetch_assoc($res);
    $count_all = intval($row['total'] ?? 0);
}

// Отображение товаров (по умолчанию — все)
$product_query = "SELECT * FROM products WHERE stock_quantity > 0";

// Обработка фильтрации по категории
if (
    $_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['action'])
    && $_GET['action'] == 'filter' && isset($_GET['id'])
) {
    $category_id = intval($_GET['id']);
    if ($category_id > 0) {
        $product_query =
            "SELECT * FROM products WHERE category_id = $category_id AND stock_quantity > 0";
    }
}

// Обработка сброса фильтра
if (
    $_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['action'])
    && $_GET['action'] == 'reset'
) {
    // по умолчанию (все товары)
}

//Получаем все категории
$categories_query = "SELECT * FROM categories";
$categories_result = mysqli_query($connect, $categories_query);

// Обработка поиска
$search = '';
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search = trim($_GET['search']);

    // Экранируем специальные символы для безопасности
    $search = mysqli_real_escape_string($connect, $search);

    // Поиск товаров, где название содержит введённую строку
    $product_query = "SELECT * FROM products WHERE stock_quantity > 0 AND `name` LIKE '%$search%'";
}
// Выполняем запрос к товарам
$result = mysqli_query($connect, $product_query);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/css/style.css">
    <title>Главная страница</title>
</head>

<body>
    <div class="container">
        <header>
            <div class="header-actions">
                <a class="cart-icon" href="cart.php">
                    <img src="/photo/cart.png" alt="корзина">
                    <span class="cart-badge"><?= $count_all ?></span>
                </a>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a class="orders-icon" href="my_orders.php">
                        <img src="/photo/orders.png" alt="Мои покупки">
                    </a>
                <?php endif; ?>
                <?php if (
                    isset($_SESSION['role']) &&
                    $_SESSION['role'] == 'admin'
                ): ?>
                    <a class="orders-icon" href="settings.php">
                        <img src="/photo/settings.png" alt="Настройки">
                    </a>
                <?php endif; ?>
            </div>

            <h1>Магазин подарков ручной работы</h1>

            <?php if (isset($_SESSION['user_id'])): ?>
                <div class="out-block">
                    <img src="/photo/user.png" alt="Пользователь">
                    <p><?= htmlspecialchars($_SESSION['firstname']) ?></p>
                    <a class="out-block_a" href="logout.php">Выйти</a>
                </div>
            <?php else: ?>
                <a class="out-block_a" href="login.php">Войти</a>
            <?php endif; ?>
        </header>

        <main>
            <aside class="sidebar">
                <div class="filter-block categories-block">
                    <h3>Поиск товара</h3>
                    <form class="search-form" method="GET">
                        <div class="search-wrapper">
                            <input type="text" name="search" placeholder="Поиск...">
                            <button type="submit" class="search-button">
                                <img src="/photo/search.png" alt="Поиск">
                            </button>
                        </div>
                    </form>
                </div>

                <div class="filter-block categories-block">
                    <h3>Подарки по категориям</h3>
                    <ul>
                        <?php
                        while ($category = mysqli_fetch_assoc($categories_result)): ?>
                            <li><a href="?action=filter&id=<?= intval($category["id"]) ?>">
                                    <?= $category["name"] ?>
                                </a>
                            </li>
                        <?php endwhile; ?>
                    </ul>
                </div>
                <div class="filter-block">
                    <form class="reset-filter" method="GET">
                        <button class="filter-text" type="submit" name="action" value="reset" class="btn-reset">
                            Сбросить фильтры</button>
                    </form>
                </div>
            </aside>

            <div class="main-content">

                <!--Проверка наличия товаров в базе данных-->
                <?php if ($result && mysqli_num_rows($result) > 0): ?>

                    <div class="product-grid">
                        <!--Рендеринг списка товаров-->
                        <?php while ($product = mysqli_fetch_assoc($result)): ?>
                            <div class='product-card'>
                                <a class="link_detail" href="details_product.php?id=<?= intval($product['id']) ?>">
                                    <img src="<?= $product['image'] ?>">
                                    <h4><?= $product['name'] ?></h4>
                                    <div class="price_stock">
                                        <p><?= $product['price'] ?> &#8381;</p>
                                        <p>Осталось:
                                            <?= $product['stock_quantity'] ?>
                                        </p>
                                    </div>
                                </a>
                                <a href="?action=add&id=<?= intval($product['id']) ?>" class="btn">
                                    Добавить в корзину
                                </a>
                            </div>
                        <?php endwhile; ?>
                    </div>

                <?php else: ?>
                    <p>Нет доступных товаров.</p>
                <?php endif; ?>
            </div>
        </main>
        <footer>2025 г</footer>
    </div>
</body>

</html>