<?php
include '../../includes/config.php';


$page = 'product';
$subPage = 'manage-product';
$pageName = 'Manage Products';

// ✅ ADD PRODUCT
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_product'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $price = mysqli_real_escape_string($conn, $_POST['price']);
    $stock = mysqli_real_escape_string($conn, $_POST['stock']);
    $desc = mysqli_real_escape_string($conn, $_POST['description']);
    $imagePath = null;

    if (!empty($_FILES['image']['name'])) {
        $targetDir = "../../uploads/products/";
        if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);

        $fileName = time() . '_' . basename($_FILES['image']['name']);
        $targetFile = $targetDir . $fileName;

        if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFile)) {
            $imagePath = "uploads/products/" . $fileName;
        }
    }

    $insert = "INSERT INTO products (name, price, stock, description, image)
               VALUES ('$name', '$price', '$stock', '$desc', '$imagePath')";
    if (mysqli_query($conn, $insert)) {
        $uid = $_SESSION['user_id'];
        mysqli_query($conn, "INSERT INTO logs (user_id, action) VALUES ('$uid', 'Added product: $name')");
        $_SESSION['success_message'] = "✅ Product added successfully!";
        header("Location: manage-product.php");
        exit();
    }
}

// ✅ UPDATE PRODUCT
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_product'])) {
    $id = $_POST['product_id'];
    $name = mysqli_real_escape_string($conn, $_POST['edit_name']);
    $price = mysqli_real_escape_string($conn, $_POST['edit_price']);
    $stock = mysqli_real_escape_string($conn, $_POST['edit_stock']);
    $desc = mysqli_real_escape_string($conn, $_POST['edit_description']);

    $updateQuery = "UPDATE products SET name='$name', price='$price', stock='$stock', description='$desc'";

    // Handle image update
    if (!empty($_FILES['edit_image']['name'])) {
        $targetDir = "../../uploads/products/";
        if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);

        $fileName = time() . '_' . basename($_FILES['edit_image']['name']);
        $targetFile = $targetDir . $fileName;
        if (move_uploaded_file($_FILES['edit_image']['tmp_name'], $targetFile)) {
            $imagePath = "uploads/products/" . $fileName;
            $updateQuery .= ", image='$imagePath'";
        }
    }

    $updateQuery .= " WHERE product_id='$id'";
    if (mysqli_query($conn, $updateQuery)) {
        $uid = $_SESSION['user_id'];
        mysqli_query($conn, "INSERT INTO logs (user_id, action) VALUES ('$uid', 'Updated product: $name')");
        $_SESSION['success_message'] = "✅ Product updated successfully!";
        header("Location: manage-product.php");
        exit();
    }
}

// ✅ DELETE PRODUCT
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $result = mysqli_query($conn, "SELECT name, image FROM products WHERE product_id='$id'");
    $row = mysqli_fetch_assoc($result);
    $name = $row['name'];
    $image = "../../" . $row['image'];

    // Delete image if exists
    if (file_exists($image)) unlink($image);

    mysqli_query($conn, "DELETE FROM products WHERE product_id='$id'");
    $uid = $_SESSION['user_id'];
    mysqli_query($conn, "INSERT INTO logs (user_id, action) VALUES ('$uid', 'Deleted product: $name')");
    $_SESSION['success_message'] = "🗑️ Product deleted successfully!";
    header("Location: manage-product.php");
    exit();
}

// Fetch all products
$products = mysqli_query($conn, "SELECT * FROM products ORDER BY product_id DESC");

include '../../template/header.php';
include '../../template/sidebar.php';
?>

<main class="app-main">
    <div class="app-content p-4">
        <div class="container-fluid">
            <h1 class="mb-4"><i class="bi bi-box-seam me-2"></i><?= $pageName ?></h1>

            <!-- ALERTS -->
            <?php if (isset($_SESSION['success_message'])): ?>
                <div class="alert alert-success"><?= $_SESSION['success_message'];
                                                    unset($_SESSION['success_message']); ?></div>
            <?php endif; ?>

            <!-- ADD PRODUCT -->
            <div class="card mb-4">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="bi bi-plus-circle me-2"></i>Add Product</h5>
                </div>
                <div class="card-body">
                    <form method="POST" enctype="multipart/form-data">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Product Name</label>
                                <input type="text" name="name" class="form-control" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Price (RM)</label>
                                <input type="number" name="price" step="0.01" class="form-control" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Stock</label>
                                <input type="number" name="stock" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Description</label>
                                <input type="text" name="description" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Image</label>
                                <input type="file" name="image" accept="image/*" class="form-control">
                            </div>
                        </div>
                        <div class="mt-3 text-end">
                            <button type="submit" name="add_product" class="btn btn-success">
                                <i class="bi bi-save me-1"></i>Save Product
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- PRODUCT LIST -->
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-list-ul me-2"></i>Product List</h5>
                </div>
                <div class="card-body">
                    <table id="productTable" class="table table-striped align-middle">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Image</th>
                                <th>Name</th>
                                <th>Price (RM)</th>
                                <th>Stock</th>
                                <th>Description</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $i = 1;
                            while ($row = mysqli_fetch_assoc($products)): ?>
                                <tr>
                                    <td><?= $i++ ?></td>
                                    <td>
                                        <?php if ($row['image']): ?>
                                            <img src="<?= base_url($row['image']) ?>" width="50" class="rounded">
                                        <?php else: ?>
                                            <span class="text-muted">No Image</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= htmlspecialchars($row['name']) ?></td>
                                    <td><?= number_format($row['price'], 2) ?></td>
                                    <td><?= $row['stock'] ?></td>
                                    <td><?= htmlspecialchars($row['description']) ?></td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-primary editBtn"
                                            data-id="<?= htmlspecialchars($row['product_id'], ENT_QUOTES) ?>"
                                            data-name="<?= htmlspecialchars($row['name'], ENT_QUOTES) ?>"
                                            data-price="<?= htmlspecialchars($row['price'], ENT_QUOTES) ?>"
                                            data-stock="<?= htmlspecialchars($row['stock'], ENT_QUOTES) ?>"
                                            data-description="<?= htmlspecialchars($row['description'], ENT_QUOTES) ?>">
                                            <i class="bi bi-pencil"></i>
                                        </button>

                                        <a href="?delete=<?= urlencode($row['product_id']) ?>"
                                            class="btn btn-sm btn-outline-danger"
                                            onclick="return confirm('Are you sure?')">
                                            <i class="bi bi-trash"></i>
                                        </a>
                                    </td>

                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</main>

<!-- Edit Modal -->
<div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" enctype="multipart/form-data" class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Edit Product</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="product_id" id="edit_id">
                <div class="mb-3">
                    <label class="form-label">Name</label>
                    <input type="text" name="edit_name" id="edit_name" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Price (RM)</label>
                    <input type="number" name="edit_price" id="edit_price" step="0.01" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Stock</label>
                    <input type="number" name="edit_stock" id="edit_stock" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Description</label>
                    <input type="text" name="edit_description" id="edit_description" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Image (optional)</label>
                    <input type="file" name="edit_image" accept="image/*" class="form-control">
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" name="update_product" class="btn btn-primary">Update</button>
            </div>
        </form>
    </div>
</div>

<?php include '../../template/footer.php'; ?>
<script>
    new DataTable('#productTable');

    // Populate edit modal
    document.querySelectorAll('.editBtn').forEach(btn => {
        btn.addEventListener('click', () => {
            document.getElementById('edit_id').value = btn.dataset.id;
            document.getElementById('edit_name').value = btn.dataset.name;
            document.getElementById('edit_price').value = btn.dataset.price;
            document.getElementById('edit_stock').value = btn.dataset.stock;
            document.getElementById('edit_description').value = btn.dataset.description;
            new bootstrap.Modal(document.getElementById('editModal')).show();
        });
    });
</script>