<?php
require_once '../../includes/config.php';
include '../customer/header.php';

// 🔹 Validate product ID
if (empty($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "<div class='container py-5 text-center'><h4>❌ Product not found.</h4></div>";
    include '../customer/footer.php';
    exit();
}

$product_id = intval($_GET['id']);
$productQuery = mysqli_query($conn, "SELECT * FROM products WHERE product_id = '$product_id'");

include '../../function/recommed-products.php';

if (!$productQuery || mysqli_num_rows($productQuery) == 0) {
    echo "<div class='container py-5 text-center'><h4>❌ Product not found.</h4></div>";
    include '../customer/footer.php';
    exit();
}

$product = mysqli_fetch_assoc($productQuery);

$variantQuery = mysqli_query($conn, "
    SELECT 
        o.option_name, 
        o.option_value, 
        o.variant_id,
        o.variant_image_url
    FROM variant_options o
    INNER JOIN product_variants v ON o.variant_id = v.variant_id
    WHERE v.product_id = '$product_id'
");


$availableOptions = [];
while ($row = mysqli_fetch_assoc($variantQuery)) {
    $name = $row['option_name'];
    $value = $row['option_value'];
    $availableOptions[$name][] = $value;
}

foreach ($availableOptions as $k => $vals) {
    $availableOptions[$k] = array_values(array_unique($vals));
}

// Fetch variants again (already queried above)
mysqli_data_seek($variantQuery, 0);

$colorImageMap = [];

while ($row = mysqli_fetch_assoc($variantQuery)) {
    if (strtolower($row['option_name']) === "color" && !empty($row['variant_image_url'])) {
        $color = $row['option_value'];
        $colorImageMap[$color] = base_url($row['variant_image_url']);
    }
}

// Now define image path BEFORE generating JS map
$imagePath = !empty($product['image'])
    ? base_url($product['image'])
    : base_url('assets/img/no_image.png');

?>

<div class="container py-5">
    <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
        <div class="row g-0">
            <div class="col-md-6 bg-light p-4">
                <!-- BACK BUTTON -->
                <a href="<?= base_url('view/shop-listing.php') ?>"
                    class="btn btn-outline-secondary px-4 mb-2"
                    style="top: 20px; left: 20px; z-index: 10;">
                    <i class="bi bi-arrow-left"></i> Product List
                </a>
                <div class="position-relative d-inline-block w-100 text-center">
                    <!-- PRODUCT IMAGE -->
                    <img id="mainProductImage"
                        src="<?= $imagePath ?>"
                        alt="<?= htmlspecialchars($product['name']) ?>"
                        class="img-fluid rounded-4 shadow"
                        style="max-height: 400px; object-fit: contain; width: 100%;">
                </div>
            </div>

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



                <?php if (isset($_SESSION['user_id'])): ?>
                    <?php if ($product['stock'] > 0): ?>
                        <!-- <form action="../../function/add-to-cart.php" method="POST" class="d-flex flex-column gap-3"> -->
                        <form action="../../function/add-to-cart-db.php" method="POST" class="d-flex flex-column gap-3">

                            <input type="hidden" name="product_id" value="<?= $product['product_id'] ?>">
                            <input type="hidden" name="name" value="<?= htmlspecialchars($product['name']) ?>">
                            <input type="hidden" name="price" value="<?= $product['price'] ?>">
                            <input type="hidden" name="image" value="<?= htmlspecialchars($product['image']) ?>">

                            <?php if (!empty($availableOptions)): ?>
                                <div>
                                    <?php foreach ($availableOptions as $optionName => $values): ?>
                                        <div class="mb-3">
                                            <label class="form-label fw-semibold"><?= htmlspecialchars($optionName) ?>:</label>

                                            <?php if (strtolower($optionName) === 'color'): ?>
                                                <div class="d-flex align-items-center flex-wrap gap-2">
                                                    <?php foreach ($values as $idx => $color):
                                                        $cid = 'color_' . $idx;
                                                    ?>
                                                        <input id="<?= $cid ?>" type="radio" name="color"
                                                            value="<?= htmlspecialchars($color) ?>"
                                                            class="d-none" <?= $idx === 0 ? 'checked' : '' ?>>
                                                        <label for="<?= $cid ?>"
                                                            class="border border-2 rounded-circle"
                                                            style="width:28px;height:28px;background:<?= htmlspecialchars($color) ?>;cursor:pointer;">
                                                        </label>
                                                    <?php endforeach; ?>
                                                </div>
                                            <?php else: ?>
                                                <select name="<?= strtolower(preg_replace('/\s+/', '_', $optionName)) ?>"
                                                    class="form-select w-auto mt-1">
                                                    <?php foreach ($values as $value): ?>
                                                        <option value="<?= htmlspecialchars($value) ?>">
                                                            <?= htmlspecialchars($value) ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            <?php endif; ?>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>

                            <div class="d-flex align-items-center gap-2">
                                <div class="input-group" style="width:140px;">
                                    <button type="button" class="btn btn-outline-secondary btn-sm" onclick="decreaseQty(this)">−</button>
                                    <input type="number" name="quantity" class="form-control text-center"
                                        value="1" min="1" max="<?= $product['stock'] ?>">
                                    <button type="button" class="btn btn-outline-secondary btn-sm" onclick="increaseQty(this)">+</button>
                                </div>

                                <!-- <button type="submit" class="btn btn-success px-2"> -->
                                <button type="submit" class="btn btn-add-cart">
                                    <i class="bi bi-cart-plus"></i> Add to Cart
                                </button>
                                <!-- <a href="<?= base_url('view/chat/chat-now.php?product_id=' . $product['product_id']) ?>"
                                    class="btn btn-warning px-4"> -->
                                <a href="<?= base_url('view/chat/chat-now.php?product_id=' . $product['product_id']) ?>"
                                    class="btn btn-message">
                                    <i class="bi bi-chat-right-text-fill"></i> Chat Now
                                </a>

                            </div>
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

    <?php if (!empty($alsoBuy)): ?>
        <div class="container my-5">
            <div class="text-center mb-4">
                <h3 class="fw-bold text-uppercase">People Also Buy This Product</h3>
            </div>

            <div class="row g-4 justify-content-center">
                <?php foreach ($alsoBuy as $item): ?>
                    <div class="col-6 col-md-4 col-lg-3">
                        <a href="<?= base_url('view/customer/product-detail.php?id=' . $item['product_id']) ?>" class="text-decoration-none d-block h-100">
                            <div class="card shadow-sm border h-100 transition-shadow-hover">
                                <div class="card-img-container" style="height: 180px; overflow: hidden; display: flex; align-items: center; justify-content: center; background-color: #f8f9fa;">
                                    <img src="<?= base_url($item['image']) ?>"
                                        class="card-img-top"
                                        alt="<?= htmlspecialchars($item['name']) ?>"
                                        style="max-height: 100%; width: auto; max-width: 100%; object-fit: contain; padding: 10px;">
                                </div>

                                <div class="card-body d-flex flex-column">
                                    <h6 class="card-title text-dark fw-semibold mb-2 line-clamp-2"><?= htmlspecialchars($item['name']) ?></h6>
                                    <p class="text-success fw-bolder mt-auto mb-0">RM <?= number_format($item['price'], 2) ?></p>
                                </div>
                            </div>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="text-center mt-5">
                <a href="<?= base_url('view/shop-listing.php') ?>" class=" btn btn-send btn-lg">See Other Products</a>
            </div>
        </div>
    <?php endif; ?>

</div>

<div class="modal fade" id="loginPromptModal" tabindex="-1" aria-labelledby="loginPromptLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header bg-warning text-dark rounded-top-4">
                <h5 class="modal-title" id="loginPromptLabel">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>Login Required
                </h5>
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

<script>
    function increaseQty(btn) {
        const input = btn.parentElement.querySelector('input[type="number"]');
        input.value = Math.min(parseInt(input.max) || 9999, parseInt(input.value || 1) + 1);
    }

    function decreaseQty(btn) {
        const input = btn.parentElement.querySelector('input[type="number"]');
        input.value = Math.max(parseInt(input.min) || 1, parseInt(input.value || 1) - 1);
    }

    document.addEventListener("DOMContentLoaded", () => {
        const mainImage = document.getElementById("mainProductImage");
        const defaultImage = "<?= $imagePath ?>"; // product table image
        const variantImages = <?= json_encode($colorImageMap) ?>; // color → image

        function updateImage() {
            const selectedColor = document.querySelector("input[name=color]:checked");
            if (!selectedColor) return;

            let color = selectedColor.value;

            if (variantImages[color]) {
                mainImage.src = variantImages[color];
            } else {
                mainImage.src = defaultImage;
            }
        }

        // Apply when selecting color
        document.querySelectorAll("input[name=color]").forEach(el => {
            el.addEventListener("change", updateImage);
        });

        // RESET BUTTON → go back to product image
        document.getElementById("resetImageBtn").addEventListener("click", () => {
            mainImage.src = defaultImage;

            // unselect color
            const checked = document.querySelector("input[name=color]:checked");
            if (checked) checked.checked = false;
        });

        updateImage(); // first load
    });
</script>

<?php include '../customer/footer.php'; ?>