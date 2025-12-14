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

$sql = "
SELECT 
    oi2.product_id,
    p.name,
    p.price,
    p.image,
    SUM(oi2.quantity) AS total_qty
FROM order_items oi
INNER JOIN order_items oi2 
    ON oi.order_id = oi2.order_id
INNER JOIN products p 
    ON p.product_id = oi2.product_id
WHERE oi.product_id = '$product_id'
  AND oi2.product_id != '$product_id'
GROUP BY oi2.product_id, p.name, p.price, p.image
ORDER BY total_qty DESC
LIMIT 4
";

$q = mysqli_query($conn, $sql);

while ($r = mysqli_fetch_assoc($q)) {
    $alsoBuy[] = $r;
}
