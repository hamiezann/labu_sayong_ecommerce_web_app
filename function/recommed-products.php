<?php

$user_id = $_SESSION['user_id'] ?? null;
$userPurchased = [];
if ($user_id) {
    $q = mysqli_query($conn, "
        SELECT DISTINCT product_id 
        FROM order_items oi
        INNER JOIN orders o ON oi.order_id = o.order_id
        WHERE o.user_id = '$user_id'
    ");

    while ($r = mysqli_fetch_assoc($q)) {
        $userPurchased[] = $r['product_id'];
    }
}

$alsoBuy = [];

$alsoBuyQuery = mysqli_query($conn, "
    SELECT DISTINCT oi2.product_id, p.name, p.price, p.image
    FROM order_items oi
    INNER JOIN order_items oi2 ON oi.order_id = oi2.order_id
    INNER JOIN products p ON p.product_id = oi2.product_id
    INNER JOIN orders o ON oi.order_id = o.order_id
    WHERE oi.product_id = '$product_id'
      AND oi2.product_id != '$product_id'
      AND o.user_id != '$user_id'
      " . ($user_id ? " AND oi2.product_id NOT IN (" . implode(",", $userPurchased ?: [0]) . ")" : "") . "
    LIMIT 4
");

while ($r = mysqli_fetch_assoc($alsoBuyQuery)) {
    $alsoBuy[] = $r;
}

if (empty($alsoBuy)) {
    $exclude = array_merge([$product_id], $userPurchased);
    $excludeList = implode(",", $exclude ?: [0]);

    $fallbackQuery = mysqli_query($conn, "
        SELECT product_id, name, price, image 
        FROM products
        WHERE product_id NOT IN ($excludeList)
        ORDER BY RAND()
        LIMIT 4
    ");

    while ($r = mysqli_fetch_assoc($fallbackQuery)) {
        $alsoBuy[] = $r;
    }
}
