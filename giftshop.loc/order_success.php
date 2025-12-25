<?php
include 'config.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['order_id'])) {
    $order_id = intval($_POST['order_id']);
    $user_id = intval($_SESSION['user_id'] ?? 0);

    if (!$user_id) {
        header('Location: login.php');
        exit();
    }

    // Проверяем, что заказ принадлежит пользователю
    $result = mysqli_query(
        $connect,
        "SELECT id FROM orders 
        WHERE id = $order_id AND user_id = $user_id AND is_paid = 0;"
    );

    if (mysqli_num_rows($result) > 0) {
        //Обновляем статус
        mysqli_query(
            $connect,
            "UPDATE orders 
         SET is_paid = 1 WHERE id = $order_id;"
        );
    } else {
        exit("Заказ не найден или уже оплачен");
    }

    // Перенаправляем на страницу успеха
    header("Location: success.php?order_id=$order_id");
    exit();
}
?>