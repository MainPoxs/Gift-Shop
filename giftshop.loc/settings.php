<?php
include 'config.php';
session_start();

$message = '';
$error = '';

if (isset($_SESSION['role']) && $_SESSION['role'] == 'admin') {
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {

        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $price = $_POST['price'] ?? 0;
        $stock = intval($_POST['stock_quantity'] ?? 0);
        $category_id = intval($_POST['category_id'] ?? 0);

        $filename = $_FILES['image']['name'];
        $imagePath = 'photo/' . $filename;
        move_uploaded_file($_FILES['image']['tmp_name'], $imagePath);

        if (!$error) {
            $res = mysqli_query(
                $connect,
                "INSERT INTO products (image, name, description, price, stock_quantity, category_id) 
                VALUES ('$imagePath', '$name', '$description', $price, $stock, $category_id)"
            );

            if ($res) {
                $message = '&#9989; Товар успешно добавлен!';

                // Сброс формы после успеха
                $_POST = [];
            } else {
                $error = 'Ошибка при сохранении в базу данных.';
            }
        }
    }
} else {
    header('Location: login.php');
    exit();
}

$categories = mysqli_query($connect, "SELECT id, name FROM categories ORDER BY name");
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/css/style_admin.css">
    <title>Администратор/Добавить товар</title>
</head>

<body>

    <div class="container">
        <h2>Добавить новый товар</h2>

        <?php if ($message): ?>
            <div class="alert alert-success"><?= $message ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="name">Название товара *</label>
                <input type="text" id="name" name="name" required>
            </div>

            <div class="form-group">
                <label for="description">Описание *</label>
                <textarea id="description" name="description" required></textarea>
            </div>

            <div class="form-group">
                <label for="price">Цена &#8381; *</label>
                <input type="number" id="price" name="price" required>
            </div>

            <div class="form-group">
                <label for="stock_quantity">Количество на складе *</label>
                <input type="number" id="stock_quantity" name="stock_quantity" min="0" required>
            </div>

            <div class="form-group">
                <label for="category_id">Категория *</label>
                <select id="category_id" name="category_id" required>
                    <option value="">— Выберите категорию —</option>
                    <?php while ($category = mysqli_fetch_assoc($categories)): ?>
                        <option value="<?= intval($category['id']) ?>">
                            <?= htmlspecialchars($category['name']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="image">Изображение</label>
                <input type="file" id="image" name="image" required>
            </div>

            <button type="submit" class="btn">Добавить товар в каталог</button>
        </form>

        <div class="nav-links">
            <a href="index.php">
                <- В магазин </a>
        </div>
    </div>

</body>

</html>