<?php
ob_start();
include '../customer/header.php';

// ✅ Handle remove item
if (isset($_GET['remove'])) {
    $id = $_GET['remove'];
    unset($_SESSION['cart'][$id]);
    $_SESSION['success_message'] = "🗑️ Item removed from cart.";
    header("Location: cart.php");
    exit();
}

// ✅ Handle update quantities
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
    <h2 class="fw-bold mb-4 text-primary">
        <i class="bi bi-cart4 me-2"></i>My Cart
    </h2>

    <!-- ✅ Success / Empty Alerts -->
    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="alert alert-success text-center">
            <?= $_SESSION['success_message'];
            unset($_SESSION['success_message']); ?>
        </div>
    <?php endif; ?>

    <?php if (empty($cart)): ?>
        <div class="text-center py-5 border rounded bg-light">
            <i class="bi bi-bag-x display-1 text-muted"></i>
            <h4 class="mt-3 text-secondary">Your cart is empty</h4>
            <a href="../shop-listing.php" class="btn btn-primary mt-3 px-4">
                <i class="bi bi-shop"></i> Continue Shopping
            </a>
        </div>
    <?php else: ?>

        <form method="POST" class="cart-form">
            <div class="table-responsive shadow-sm">
                <table class="table align-middle text-center bg-white rounded-3">
                    <thead class="table-dark">
                        <tr>
                            <th>Product</th>
                            <th width="20%">Variant Options</th>
                            <th>Price (RM)</th>
                            <th width="12%">Quantity</th>
                            <th>Total (RM)</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $grandTotal = 0;
                        foreach ($cart as $id => $item):
                            $total = $item['price'] * $item['quantity'];
                            $grandTotal += $total;
                            $options = $item['options'] ?? [];
                        ?>
                            <tr class="align-middle">
                                <td class="text-start">
                                    <div class="d-flex align-items-center">
                                        <img src="<?= base_url($item['image']) ?>"
                                            alt="<?= htmlspecialchars($item['name']) ?>"
                                            class="rounded-3 me-3 shadow-sm"
                                            style="width: 80px; height: 80px; object-fit: cover;">
                                        <div>
                                            <h6 class="fw-semibold mb-1"><?= htmlspecialchars($item['name']) ?></h6>
                                            <small class="text-muted">Product ID: <?= htmlspecialchars($item['product_id']) ?></small>
                                        </div>
                                    </div>
                                </td>

                                <td>
                                    <?php if (!empty($options)): ?>
                                        <?php foreach ($options as $key => $value): ?>
                                            <?php if (!empty($value)): ?>
                                                <div class="text-muted small">
                                                    <span class="fw-semibold"><?= ucfirst($key) ?>:</span>
                                                    <?php if ($key === 'color'): ?>
                                                        <span class="ms-1" style="display:inline-block;width:15px;height:15px;border-radius:3px;background:<?= htmlspecialchars($value) ?>;border:1px solid #ccc;"></span>
                                                        <span><?= htmlspecialchars($value) ?></span>
                                                    <?php else: ?>
                                                        <?= htmlspecialchars($value) ?>
                                                    <?php endif; ?>
                                                </div>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <small class="text-muted">—</small>
                                    <?php endif; ?>
                                </td>

                                <td class="fw-semibold">RM <?= number_format($item['price'], 2) ?></td>

                                <td>
                                    <input type="number" name="quantity[<?= $id ?>]"
                                        value="<?= $item['quantity'] ?>"
                                        class="form-control text-center mx-auto border-primary"
                                        min="1" style="width: 70px;">
                                </td>

                                <td class="fw-bold text-success">
                                    RM <?= number_format($total, 2) ?>
                                </td>

                                <td>
                                    <a href="cart.php?remove=<?= urlencode($id) ?>"
                                        class="btn btn-sm btn-outline-danger"
                                        onclick="return confirm('Remove this item?')">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="card mt-4 shadow-sm">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">Total:
                        <span class="text-success fw-bold">RM <?= number_format($grandTotal, 2) ?></span>
                    </h4>
                    <div>
                        <button type="submit" name="update_cart" class="btn btn-outline-primary me-2">
                            <i class="bi bi-arrow-repeat me-1"></i> Update Cart
                        </button>
                        <a href="checkout.php" class="btn btn-success px-4">
                            <i class="bi bi-credit-card me-1"></i> Checkout
                        </a>
                    </div>
                </div>
            </div>
        </form>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<?php include '../customer/footer.php'; ?>
<?php ob_end_flush(); ?>