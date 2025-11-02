<?php
include '../includes/config.php';

$productId = intval($_GET['product_id']);
$result = mysqli_query($conn, "SELECT * FROM product_variants WHERE product_id = '$productId'");

if (mysqli_num_rows($result) > 0) {
    $i = 1;
    while ($row = mysqli_fetch_assoc($result)) {
        echo "<tr>
                <td>{$i}</td>
                <td>{$row['sku']}</td>
                <td>RM " . number_format($row['price'], 2) . "</td>
                <td>{$row['stock']}</td>
                <td>
                  <a href='manage-variant-options.php?variant_id={$row['variant_id']}' 
                     class='btn btn-sm btn-outline-info'>
                     <i class='bi bi-sliders'></i> Options
                  </a>
                </td>
                <td>
                  <a href='?delete_variant={$row['variant_id']}' class='btn btn-sm btn-outline-danger' 
                     onclick=\"return confirm('Delete this variant?')\">
                     <i class='bi bi-trash'></i>
                  </a>
                </td>
              </tr>";
        $i++;
    }
} else {
    echo "<tr><td colspan='6' class='text-center text-muted'>No variants yet.</td></tr>";
}
