<?php
include '../../includes/config.php';

$page = 'product';
$subPage = 'manage-product';
$pageName = 'Manage Products';

// ✅ DELETE PRODUCT
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $result = mysqli_query($conn, "SELECT name, image FROM products WHERE product_id='$id'");
    if ($row = mysqli_fetch_assoc($result)) {
        $name = $row['name'];
        $image = "../../" . $row['image'];

        // Delete image file if exists
        if (file_exists($image)) unlink($image);

        mysqli_query($conn, "DELETE FROM products WHERE product_id='$id'");
        $uid = $_SESSION['user_id'] ?? 0;
        mysqli_query($conn, "INSERT INTO logs (user_id, action) VALUES ('$uid', 'Deleted product: $name')");
        $_SESSION['success_message'] = "🗑️ Product deleted successfully!";
    }
    header("Location: manage-product.php");
    exit();
}

// ✅ Fetch all products
$products = mysqli_query($conn, "SELECT * FROM products ORDER BY product_id DESC");

include '../../template/header.php';
include '../../template/sidebar.php';
?>

<main class="app-main">
    <div class="app-content p-4">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="fw-bold mb-0"><i class="bi bi-box-seam me-2"></i><?= $pageName ?></h1>
                <a href="<?= base_url('view/staff/manage-product-details.php?action=create') ?>"
                    class="btn btn-success px-3">
                    <i class="bi bi-plus-lg me-1"></i> Create Product
                </a>
            </div>

            <!-- ✅ ALERT MESSAGES -->
            <?php if (isset($_SESSION['success_message'])): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <?= $_SESSION['success_message'];
                    unset($_SESSION['success_message']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <!-- ✅ PRODUCT LIST -->
            <div class="card shadow-sm border-0">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-list-ul me-2"></i> Product List</h5>
                </div>
                <div class="card-body">
                    <table id="productTable" class="table table-hover align-middle text-center">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Image</th>
                                <th>Name</th>
                                <th>Price (RM)</th>
                                <th>Stock</th>
                                <th>Description</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $i = 1;
                            while ($row = mysqli_fetch_assoc($products)):
                            ?>
                                <tr>
                                    <td><?= $i++ ?></td>
                                    <td>
                                        <?php if ($row['image']): ?>
                                            <img src="<?= base_url($row['image']) ?>"
                                                alt="<?= htmlspecialchars($row['name']) ?>"
                                                class="rounded shadow-sm"
                                                width="60" height="60"
                                                style="object-fit: cover;">
                                        <?php else: ?>
                                            <span class="text-muted">No Image</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="fw-semibold"><?= htmlspecialchars($row['name']) ?></td>
                                    <td>RM <?= number_format($row['price'], 2) ?></td>
                                    <td><?= $row['stock'] ?></td>
                                    <td class="text-truncate" style="max-width: 200px;">
                                        <?= htmlspecialchars($row['description']) ?>
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <!-- <a href="<?= base_url('view/staff/manage-product-details.php?id=' . $row['product_id'] . '&action=edit') ?>" -->
                                            <a href="manage-product-details.php?product_id=<?= $row['product_id'] ?>"
                                                class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-pencil"></i> Edit
                                            </a>

                                            <a href="<?= base_url('view/customer/product-detail.php?id=' . $row['product_id']) ?>"
                                                class="btn btn-sm btn-outline-secondary">
                                                <i class="bi bi-eye"></i> View
                                            </a>

                                            <a href="?delete=<?= urlencode($row['product_id']) ?>"
                                                class="btn btn-sm btn-outline-danger"
                                                onclick="return confirm('Are you sure you want to delete this product?');">
                                                <i class="bi bi-trash"></i> Delete
                                            </a>
                                        </div>
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

<?php include '../../template/footer.php'; ?>
<script>
    new DataTable('#productTable', {
        pageLength: 10,
        order: [
            [0, 'desc']
        ]
    });
</script>