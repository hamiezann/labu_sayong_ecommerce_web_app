<?php
include '../../includes/config.php';


$page = 'product';
$subPage = 'manage-product-details';
$pageName = 'Manage Product Details';

// --- 1️⃣ GET PRODUCT (for edit)
$productId = isset($_GET['product_id']) ? intval($_GET['product_id']) : 0;
$product = null;

if ($productId) {
    $product = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM products WHERE product_id = '$productId'"));
    if (!$product) {
        $_SESSION['error_message'] = "❌ Product not found!";
        header("Location: manage-product.php");
        exit();
    }
}

// --- 2️⃣ CREATE OR UPDATE PRODUCT
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_product'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $price = mysqli_real_escape_string($conn, $_POST['price']);
    $stock = mysqli_real_escape_string($conn, $_POST['stock']);
    $desc = mysqli_real_escape_string($conn, $_POST['description']);
    $imagePath = $product['image'] ?? null;

    // handle image upload
    if (!empty($_FILES['image']['name'])) {
        $targetDir = "../../uploads/products/";
        if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);
        $fileName = time() . '_' . basename($_FILES['image']['name']);
        $targetFile = $targetDir . $fileName;

        if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFile)) {
            $imagePath = "uploads/products/" . $fileName;
        }
    }

    if ($productId) {
        // update
        mysqli_query($conn, "UPDATE products SET 
            name='$name', price='$price', stock='$stock', description='$desc', image='$imagePath'
            WHERE product_id='$productId'
        ");
        $_SESSION['success_message'] = "✅ Product updated successfully!";
    } else {
        // create
        mysqli_query($conn, "INSERT INTO products (name, price, stock, description, image) 
            VALUES ('$name', '$price', '$stock', '$desc', '$imagePath')");
        $productId = mysqli_insert_id($conn);
        mysqli_query($conn, "INSERT INTO product_variants (product_id) VALUES ('$productId')");
        $_SESSION['success_message'] = "✅ Product created successfully!";
    }

    header("Location: manage-product-details.php?product_id=$productId");
    exit();
}

// --- ADD VARIANT OPTION
if (isset($_POST['add_option'])) {
    $variant = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM product_variants WHERE product_id='$productId'"));
    if (!$variant) {
        mysqli_query($conn, "INSERT INTO product_variants (product_id) VALUES ('$productId')");
        $variantId = mysqli_insert_id($conn);
    } else {
        $variantId = $variant['variant_id'];
    }

    $name = mysqli_real_escape_string($conn, $_POST['option_name']);
    $value = mysqli_real_escape_string($conn, $_POST['option_value']);
    $variantImage = null;

    if (!empty($_FILES['option_image']['name'])) {
        $targetDir = "../../uploads/products/variants/";
        if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);

        $fileName = time() . '_' . basename($_FILES['option_image']['name']);
        $targetFile = $targetDir . $fileName;

        if (move_uploaded_file($_FILES['option_image']['tmp_name'], $targetFile)) {
            $variantImage = "uploads/products/variants/" . $fileName;
        }
    }
    mysqli_query($conn, "
        INSERT INTO variant_options (variant_id, option_name, option_value, variant_image_url)
        VALUES ('$variantId', '$name', '$value', '$variantImage')
    ");
    $_SESSION['success_message'] = "✅ Variant option added!";
    header("Location: manage-product-details.php?product_id=$productId");
    exit();
}
// delete functions
if (isset($_GET['delete_option'])) {
    $optionId = intval($_GET['delete_option']);
    mysqli_query($conn, "DELETE FROM variant_options WHERE option_id='$optionId'");
    $_SESSION['success_message'] = "🗑️ Option deleted successfully!";
    header("Location: manage-product-details.php?product_id=$productId");
    exit();
}

//  FETCH VARIANTS
$options = mysqli_query($conn, "
    SELECT o.* FROM variant_options o
    JOIN product_variants v ON o.variant_id = v.variant_id
    WHERE v.product_id = '$productId'
");
?>

<?php include '../../template/header.php'; ?>
<?php include '../../template/sidebar.php'; ?>

<main class="app-main">
    <div class="app-content p-4">
        <div class="container-fluid">
            <h1 class="mb-4">
                <i class="bi bi-box-seam me-2"></i><?= $pageName ?>
            </h1>

            <!-- Alerts -->
            <?php if (isset($_SESSION['success_message'])): ?>
                <div class="alert alert-success"><?= $_SESSION['success_message'];
                                                    unset($_SESSION['success_message']); ?></div>
            <?php endif; ?>
            <?php if (isset($_SESSION['error_message'])): ?>
                <div class="alert alert-danger"><?= $_SESSION['error_message'];
                                                unset($_SESSION['error_message']); ?></div>
            <?php endif; ?>

            <!-- PRODUCT FORM -->
            <div class="card shadow-sm mb-4">
                <div class="card-header " style="background-color: #74512D; color: white">
                    <h5 class="mb-0"><?= $productId ? 'Edit Product' : 'Create Product' ?></h5>
                </div>
                <div class="card-body">
                    <form method="POST" enctype="multipart/form-data">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Product Name
                                    <span style="margin-left:4px; color: red;">*</span>
                                </label>
                                <input type="text" name="name" class="form-control" required value="<?= $product['name'] ?? '' ?>">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Price (RM)
                                    <span style="margin-left:4px; color: red;">*</span>
                                </label>
                                <input type="number" name="price" step="0.01" class="form-control" required value="<?= $product['price'] ?? '' ?>">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Stock
                                    <span style="margin-left:4px; color: red;">*</span>
                                </label>
                                <input type="number" name="stock" class="form-control" required value="<?= $product['stock'] ?? '' ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Description
                                    <span style="margin-left:4px; color: red;">*</span>
                                </label>
                                <input type="text" name="description" class="form-control" required value="<?= $product['description'] ?? '' ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Image</label>
                                <input type="file" name="image" accept="image/*" class="form-control">
                                <?php if (!empty($product['image'])): ?>
                                    <img src="<?= base_url($product['image']) ?>" width="100" class="mt-2 rounded">
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="text-end mt-3">
                            <button type="submit" name="save_product" class="btn" style="background-color: #74512D; color: white">
                                <i class="bi bi-save me-1"></i> Save
                            </button>
                            <a href="manage-product.php" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>

            <?php if ($productId): ?>
                <!-- VARIANT SECTION -->
                <div class="card shadow-sm">
                    <div class="card-header bg-dark text-white">
                        <h5 class="mb-0"><i class="bi bi-sliders me-2"></i>Variant Options</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" enctype="multipart/form-data" class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label">Option Type
                                    <span style="margin-left:4px; color: red;">*</span>
                                </label>
                                <select name="option_name" id="typeSelect" class="form-select" required onchange="toggleValueInput()">
                                    <option value="">-- Select Type --</option>
                                    <option value="Color">Color</option>
                                    <option value="Size">Size</option>
                                    <option value="Pattern">Pattern</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Option Value</label>
                                <input type="text" name="option_value" id="optionValue" class="form-control" placeholder="Enter value">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Option Image</label>
                                <input id="image-form" type="file" name="option_image" accept="image/*" class="form-control">
                            </div>
                            <div class="col-md-2 text-end align-self-end">
                                <button type="submit" name="add_option" class="btn btn-dark w-100">
                                    <i class="bi bi-plus-circle me-1"></i> Add
                                </button>
                            </div>
                        </form>

                        <hr>

                        <table class="table table-bordered align-middle mt-3">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Option Name</th>
                                    <th>Value</th>
                                    <th>Variant Image</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $i = 1;
                                while ($opt = mysqli_fetch_assoc($options)): ?>
                                    <tr>
                                        <td><?= $i++ ?></td>
                                        <td><?= htmlspecialchars($opt['option_name']) ?></td>
                                        <td>
                                            <?php if ($opt['option_name'] === 'Color'): ?>
                                                <span style="display:inline-block;width:20px;height:20px;border:1px solid #ccc;background:<?= htmlspecialchars($opt['option_value']) ?>"></span>
                                                <?= htmlspecialchars($opt['option_value']) ?>
                                            <?php else: ?>
                                                <?= htmlspecialchars($opt['option_value']) ?>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <!-- image variant -->
                                            <?php if (!empty($opt['variant_image_url'])): ?>
                                                <img src="<?= base_url($opt['variant_image_url']) ?>" width="50" class="rounded">
                                            <?php else: ?>
                                                <span class="text-muted">— No Image —</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <a href="?product_id=<?= $productId ?>&delete_option=<?= $opt['option_id'] ?>"
                                                class="btn btn-sm btn-outline-danger"
                                                onclick="return confirm('Delete this option?')">
                                                <i class="bi bi-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                                <?php if ($i === 1): ?>
                                    <tr>
                                        <td colspan="4" class="text-center text-muted">No options yet.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</main>

<?php include '../../template/footer.php'; ?>

<script>
    function toggleValueInput() {
        const select = document.getElementById('typeSelect');
        const input = document.getElementById('optionValue');
        const selected = select.value;
        const imageForm = document.getElementById('image-form');
        if (selected === 'Size' || selected === 'Pattern') {
            imageForm.style.display = 'none';
            imageForm.style = 'Background-color: Red;';
            imageForm.disabled = true;
        } else {
            imageForm.style.display = 'block';
        }
        if (selected === 'Color') {
            input.type = 'color';
            input.placeholder = '';
            input.value = '#000000';
        } else {
            input.type = 'text';
            input.placeholder = 'Enter value (e.g., Large, Floral)';
            input.value = '';
        }
    }
</script>