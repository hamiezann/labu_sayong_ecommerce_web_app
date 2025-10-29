<?php
ob_start();
include '../customer/header.php';

// Handle remove item
if (isset($_GET['remove'])) {
    $id = $_GET['remove'];
    unset($_SESSION['cart'][$id]);
    $_SESSION['success_message'] = "🗑️ Item removed from cart.";
    header("Location: cart.php");
    exit();
}

// Handle update quantities
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_cart'])) {
    foreach ($_POST['quantity'] as $id => $qty) {
        if ($qty > 0) {
            $_SESSION['cart'][$id]['quantity'] = intval($qty);
        } else {
            unset($_SESSION['cart'][$id]);
        }
    }
    $_SESSION['success_message'] = "✅ Cart updated successfully!";
    header("Location: cart.php");
    exit();
}

$cart = $_SESSION['cart'] ?? [];
?>

<div class="container py-5">
    <h2 class="fw-bold mb-4 text-primary"><i class="bi bi-cart4 me-2"></i>My Cart</h2>

    <?php if (empty($cart)): ?>
        <div class="alert alert-info text-center py-5">
            🛒 Your cart is empty.
            <br><br>
            <a href="../shop-listing.php" class="btn btn-primary">Continue Shopping</a>
        </div>
    <?php else: ?>
        <form method="POST">
            <div class="table-responsive">
                <table class="table align-middle text-center">
                    <thead class="table-dark">
                        <tr>
                            <th>Image</th>
                            <th>Product</th>
                            <th>Price (RM)</th>
                            <th>Quantity</th>
                            <th>Total (RM)</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $grandTotal = 0;
                        foreach ($cart as $id => $item):
                            $total = $item['price'] * $item['quantity'];
                            $grandTotal += $total;
                        ?>
                            <tr>
                                <td>
                                    <img src="<?= base_url($item['image']) ?>" alt="<?= htmlspecialchars($item['name']) ?>"
                                        class="rounded-3" style="width: 70px; height: 70px; object-fit: cover;">
                                </td>
                                <td><?= htmlspecialchars($item['name']) ?></td>
                                <td><?= number_format($item['price'], 2) ?></td>
                                <td>
                                    <input type="number" name="quantity[<?= $id ?>]" value="<?= $item['quantity'] ?>"
                                        class="form-control text-center" min="1" style="width: 80px;">
                                </td>
                                <td><?= number_format($total, 2) ?></td>
                                <td>
                                    <a href="cart.php?remove=<?= $id ?>" class="btn btn-sm btn-danger">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-between align-items-center mt-4">
                <h4>Total: <span class="text-success fw-bold">RM <?= number_format($grandTotal, 2) ?></span></h4>
                <div>
                    <button type="submit" name="update_cart" class="btn btn-outline-primary me-2">
                        <i class="bi bi-arrow-repeat"></i> Update Cart
                    </button>
                    <a href="checkout.php" class="btn btn-success px-4">
                        <i class="bi bi-credit-card"></i> Checkout
                    </a>
                </div>
            </div>
        </form>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<?php
include '../customer/footer.php';
ob_end_flush();
?>