<?php
ob_start();
include '../customer/header.php';
require_once '../../includes/config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// --- STEP 1: Fetch user's cart ---
$cartQuery = $conn->prepare("
    SELECT c.cart_id 
    FROM carts c 
    WHERE c.user_id = ? 
    LIMIT 1
");
$cartQuery->bind_param("i", $user_id);
$cartQuery->execute();
$cartQuery->bind_result($cart_id);
$hasCart = $cartQuery->fetch();
$cartQuery->close();

$cartItems = [];
if ($hasCart) {
    $itemsQuery = $conn->prepare("
        SELECT ci.cart_item_id, ci.product_id, ci.variant_id, ci.quantity, ci.price, ci.color, ci.size, ci.pattern, p.name, p.image, p.stock
        FROM cart_items ci
        JOIN products p ON ci.product_id = p.product_id
        WHERE ci.cart_id = ?
    ");
    $itemsQuery->bind_param("i", $cart_id);
    $itemsQuery->execute();
    $result = $itemsQuery->get_result();
    while ($row = $result->fetch_assoc()) {
        $cartItems[$row['cart_item_id']] = $row;
    }
    $itemsQuery->close();
}

// --- STEP 2: Handle remove item ---
if (isset($_GET['remove'])) {
    $removeId = intval($_GET['remove']);
    $deleteItem = $conn->prepare("DELETE FROM cart_items WHERE cart_item_id = ? AND cart_id = ?");
    $deleteItem->bind_param("ii", $removeId, $cart_id);
    $deleteItem->execute();
    $deleteItem->close();

    $_SESSION['success_message'] = "🗑️ Item removed from cart.";
    header("Location: cart.php");
    exit();
}

// --- STEP 3: Handle update cart ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_cart'])) {
    foreach ($_POST['quantity'] as $cartItemId => $qty) {
        $qty = intval($qty);

        if (!isset($cartItems[$cartItemId])) continue;

        $productStock = $cartItems[$cartItemId]['stock'];

        if ($qty > $productStock) {
            $_SESSION['error_message'] = "⚠️ Requested quantity exceeds available stock ({$productStock}).";
            header("Location: cart.php");
            exit();
        }

        if ($qty > 0) {
            $updateStmt = $conn->prepare("UPDATE cart_items SET quantity = ? WHERE cart_item_id = ? AND cart_id = ?");
            $updateStmt->bind_param("iii", $qty, $cartItemId, $cart_id);
            $updateStmt->execute();
            $updateStmt->close();
        } else {
            // Remove item if quantity <= 0
            $deleteStmt = $conn->prepare("DELETE FROM cart_items WHERE cart_item_id = ? AND cart_id = ?");
            $deleteStmt->bind_param("ii", $cartItemId, $cart_id);
            $deleteStmt->execute();
            $deleteStmt->close();
        }
    }

    $_SESSION['success_message'] = "✅ Cart updated successfully!";
    header("Location: cart.php");
    exit();
}
?>

<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4 p-3 rounded-3 shadow-sm"
        style="background:#fff;">
        <h3 class="fw-bold mb-0 d-flex align-items-center" style="color:#222;">
            <i class="bi bi-receipt-cutoff me-2 fs-4"></i>
            My Cart
        </h3>
    </div>
    <!-- <h2 class="fw-bold mb-4 text-primary">
        <i class="bi bi-cart4 me-2"></i>My Cart
    </h2> -->

    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="alert alert-success text-center">
            <?= $_SESSION['success_message'];
            unset($_SESSION['success_message']); ?>
        </div>
    <?php endif; ?>
    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="alert alert-danger text-center">
            <?= $_SESSION['error_message'];
            unset($_SESSION['error_message']); ?>
        </div>
    <?php endif; ?>

    <?php if (empty($cartItems)): ?>
        <div class="text-center py-5 border rounded bg-light">
            <i class="bi bi-bag-x display-1 text-muted"></i>
            <h4 class="mt-3 text-secondary">Your cart is empty</h4>
            <a href="../shop-listing.php" class="btn btn-send mt-3 px-4" style="color: white">
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
                        foreach ($cartItems as $id => $item):
                            $total = $item['price'] * $item['quantity'];
                            $grandTotal += $total;
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
                                    <?php foreach (['color', 'size', 'pattern'] as $opt): ?>
                                        <?php if (!empty($item[$opt])): ?>
                                            <div class="text-muted small">
                                                <span class="fw-semibold"><?= ucfirst($opt) ?>:</span>
                                                <?php if ($opt === 'color'): ?>
                                                    <span class="ms-1" style="display:inline-block;width:15px;height:15px;border-radius:3px;background:<?= htmlspecialchars($item[$opt]) ?>;border:1px solid #ccc;"></span>
                                                    <span><?= htmlspecialchars($item[$opt]) ?></span>
                                                <?php else: ?>
                                                    <?= htmlspecialchars($item[$opt]) ?>
                                                <?php endif; ?>
                                            </div>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </td>

                                <td class="fw-semibold">RM <?= number_format($item['price'], 2) ?></td>

                                <td>
                                    <input type="number" name="quantity[<?= $id ?>]"
                                        value="<?= $item['quantity'] ?>"
                                        class="form-control text-center mx-auto border-primary"
                                        min="1" max="<?= $item['stock'] ?>" style="width: 70px;">
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
                        <a href="checkout.php" class="btn back-success-custom px-4" style="color: white;">
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