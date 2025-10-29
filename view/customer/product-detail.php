<?php
// require_once 'header.php';
require_once '../../includes/config.php'; // adjust path if needed
include '../customer/header.php';

// Check if ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo "<div class='container py-5 text-center'><h4>❌ Product not found.</h4></div>";
    require_once 'footer.php';
    exit();
}

$product_id = intval($_GET['id']);
$query = mysqli_query($conn, "SELECT * FROM products WHERE product_id = '$product_id'");

if (!$query || mysqli_num_rows($query) == 0) {
    echo "<div class='container py-5 text-center'><h4>❌ Product not found.</h4></div>";
    require_once 'footer.php';
    exit();
}

$product = mysqli_fetch_assoc($query);
$imagePath = !empty($product['image']) ? base_url($product['image']) : base_url('assets/img/no_image.png');
?>

<div class="container py-5">
    <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
        <div class="row g-0">
            <!-- Product Image -->
            <div class="col-md-6 text-center bg-light p-4">
                <img src="<?= $imagePath ?>" alt="<?= htmlspecialchars($product['name']) ?>"
                    class="img-fluid rounded-4 shadow"
                    style="max-height: 400px; object-fit: contain;">
            </div>

            <!-- Product Details -->
            <div class="col-md-6 p-5">
                <h2 class="fw-bold mb-3 text-primary"><?= htmlspecialchars($product['name']) ?></h2>
                <p class="text-muted mb-4"><?= htmlspecialchars($product['description']) ?></p>

                <h4 class="text-success fw-semibold mb-3">RM <?= number_format($product['price'], 2) ?></h4>

                <p class="mb-3">
                    <strong>Stock:</strong>
                    <?= $product['stock'] > 0
                        ? "<span class='text-success'>{$product['stock']} available</span>"
                        : "<span class='text-danger'>Out of stock</span>" ?>
                </p>

                <div class="d-flex gap-3 mt-4">
                    <a href="<?= base_url('view/shop-listing.php') ?>" class="btn btn-outline-secondary px-4">
                        <i class="bi bi-arrow-left"></i> Product List
                    </a>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <?php if ($product['stock'] > 0): ?>
                            <form action="../../function/add-to-cart.php" method="POST" class="d-inline-flex align-items-center gap-2">
                                <input type="hidden" name="product_id" value="<?= $product['product_id'] ?>">
                                <input type="hidden" name="name" value="<?= htmlspecialchars($product['name']) ?>">
                                <input type="hidden" name="price" value="<?= $product['price'] ?>">
                                <input type="hidden" name="image" value="<?= htmlspecialchars($product['image']) ?>">

                                <div class="input-group" style="width:130px;">
                                    <button type="button" class="btn btn-outline-secondary btn-sm" onclick="decreaseQty(this)">−</button>
                                    <input type="number" name="quantity" class="form-control text-center" value="1" min="1" max="<?= $product['stock'] ?>">
                                    <button type="button" class="btn btn-outline-secondary btn-sm" onclick="increaseQty(this)">+</button>
                                </div>

                                <button type="submit" class="btn btn-success px-4">
                                    <i class="bi bi-cart-plus"></i> Add to Cart
                                </button>
                            </form>
                        <?php else: ?>
                            <button class="btn btn-secondary" disabled>Out of Stock</button>
                        <?php endif; ?>
                    <?php else: ?>
                        <button type="button" class="btn btn-success px-4" data-bs-toggle="modal" data-bs-target="#loginPromptModal">
                            <i class="bi bi-cart-plus"></i> Add to Cart
                        </button>
                    <?php endif; ?>

                </div>
            </div>
        </div>
    </div>
</div>

<!-- Login Prompt Modal -->
<div class="modal fade" id="loginPromptModal" tabindex="-1" aria-labelledby="loginPromptLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header bg-warning text-dark rounded-top-4">
                <h5 class="modal-title" id="loginPromptLabel"><i class="bi bi-exclamation-triangle-fill me-2"></i>Login Required</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center py-4">
                <p>You need to log in before adding items to your cart.</p>
            </div>
            <div class="modal-footer justify-content-center border-0 pb-4">
                <a href="<?= base_url('view/auth/login.php') ?>" class="btn btn-primary px-4">Login Now</a>
                <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Cancel</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<?php
// require_once 'footer.php';
include '../customer/footer.php';
?>