<?php include 'config.php';
session_start();

if (
    isset($_SESSION['user_id']) &&
    $_SERVER['REQUEST_METHOD'] == 'POST'
) {
    $user_id = intval($_SESSION['user_id']);
    $total_amount = $_POST['total_amount'];
    $shipping_address = $_POST['shipping_address'];
    $phone = $_POST['phone'];
    $recipient_name = $_POST['recipient_name'];
    $recipient_email = $_POST['recipient_email'];

    //Создаем заказ
    mysqli_query(
        $connect,
        "INSERT INTO orders (user_id, total_amount, shipping_address,
        phone, recipient_name, recipient_email)
        VALUES ($user_id, $total_amount, '$shipping_address',
        '$phone', '$recipient_name', '$recipient_email');"
    );

    //Получаем id заказа
    $order_id = mysqli_insert_id($connect);

    // Получаем товары из корзины
    $cart_items = mysqli_query($connect, "
    SELECT c.product_id, c.quantity, p.price
    FROM carts AS c
    JOIN products AS p ON c.product_id = p.id
    WHERE c.user_id = $user_id;");

    while ($item = mysqli_fetch_assoc($cart_items)) {
        mysqli_query(
            $connect,
            "INSERT INTO order_items (order_id, product_id, quantity, price_per_unit)
            VALUES ($order_id, $item[product_id], $item[quantity], $item[price]);"
        );
    }

    //Очищаем корзину
    mysqli_query($connect, "DELETE FROM carts WHERE user_id = $user_id");

    header("Location: pay.php?order_id=$order_id");
    exit();
} else {
    header('Location: checkout.php');
    exit();
}
?>