<?php
require_once '../includes/config.php';
include '../view/customer/header.php';
$title = "Shop - Labu Sayong";
// Get filter parameters
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$minPrice = isset($_GET['min_price']) ? floatval($_GET['min_price']) : 0;
$maxPrice = isset($_GET['max_price']) ? floatval($_GET['max_price']) : 10000;

// Build query with filters
$query = "SELECT * FROM products WHERE 1=1";

if (!empty($search)) {
    $query .= " AND (name LIKE '%$search%' OR description LIKE '%$search%')";
}

$query .= " AND price BETWEEN $minPrice AND $maxPrice";
$query .= " ORDER BY created_at DESC";

$productList = mysqli_query($conn, $query);

// if (isset($_SESSION['id'])) {
//     $userId = $_SESSION['id'];
//     $userData = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM users WHERE id='$userId'"));
// }

?>




<!-- PAGE HEADER -->
<section class="page-header">
    <div class="container">
        <h1>Explore Our Collection</h1>
        <p class="lead">Discover authentic handcrafted traditional pottery</p>
    </div>
</section>

<!-- SEARCH AND FILTER SECTION -->
<div class="container">
    <div class="filter-section">
        <form method="GET" action="">
            <div class="row align-items-end g-3">
                <div class="col-md-6">
                    <div class="search-box">
                        <i class="bi bi-search"></i>
                        <input type="text"
                            name="search"
                            class="form-control"
                            placeholder="Search for products..."
                            value="<?= htmlspecialchars($search) ?>">
                    </div>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-search w-100">
                        <i class="bi bi-search me-2"></i> Search
                    </button>
                </div>
                <div class="col-md-2">
                    <a href="<?= base_url('view/shop-listing.php') ?>" class="btn btn-reset w-100">
                        <i class="bi bi-arrow-clockwise me-2"></i> Reset
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- PRODUCTS CONTAINER -->
<div class="container products-container">
    <div class="row">
        <!-- FILTER SIDEBAR -->
        <div class="col-lg-3">
            <div class="filter-card">
                <h5><i class="bi bi-funnel"></i> Filter by Budget</h5>

                <form method="GET" action="" id="filterForm">
                    <input type="hidden" name="search" value="<?= htmlspecialchars($search) ?>">

                    <div class="price-range-inputs">
                        <div class="price-input">
                            <label>Min Price (RM)</label>
                            <input type="number"
                                name="min_price"
                                class="form-control"
                                placeholder="0"
                                value="<?= $minPrice ?>"
                                min="0">
                        </div>
                        <div class="price-input">
                            <label>Max Price (RM)</label>
                            <input type="number"
                                name="max_price"
                                class="form-control"
                                placeholder="10000"
                                value="<?= $maxPrice ?>"
                                min="0">
                        </div>
                    </div>

                    <button type="submit" class="btn btn-search w-100 mb-3">
                        Apply Filter
                    </button>

                    <div class="mb-2">
                        <small class="text-muted fw-500">Quick Filters:</small>
                    </div>
                    <div class="quick-filters">
                        <button type="button" class="quick-filter-btn" onclick="setRange(0, 50)">
                            Under RM50
                        </button>
                        <button type="button" class="quick-filter-btn" onclick="setRange(50, 100)">
                            RM50 - RM100
                        </button>
                        <button type="button" class="quick-filter-btn" onclick="setRange(100, 200)">
                            RM100 - RM200
                        </button>
                        <button type="button" class="quick-filter-btn" onclick="setRange(200, 500)">
                            RM200 - RM500
                        </button>
                        <button type="button" class="quick-filter-btn" onclick="setRange(500, 10000)">
                            Above RM500
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- PRODUCTS GRID -->
        <div class="col-lg-9">
            <div class="results-header">
                <div class="results-count">
                    <strong><?= mysqli_num_rows($productList) ?></strong> products found
                </div>
                <!-- <select class="sort-select">
                    <option>Sort by: Latest</option>
                    <option>Price: Low to High</option>
                    <option>Price: High to Low</option>
                    <option>Name: A to Z</option>
                </select> -->
            </div>

            <?php if (mysqli_num_rows($productList) > 0): ?>
                <div class="row g-4">
                    <?php while ($product = mysqli_fetch_assoc($productList)):
                        $imagePath = !empty($product['image']) ? base_url($product['image']) : base_url('assets/img/no_image.png');
                    ?>
                        <div class="col-lg-4 col-md-6">
                            <div class="card product-card position-relative">
                                <span class="product-badge">Handcrafted</span>
                                <img src="<?= $imagePath ?>" alt="<?= htmlspecialchars($product['name']) ?>">
                                <div class="card-body">
                                    <h5><?= htmlspecialchars($product['name']) ?></h5>
                                    <p class="text-muted mb-0" style="font-size: 0.9rem;">Traditional Craft</p>
                                    <p class="product-price">RM <?= number_format($product['price'], 2) ?></p>
                                    <!-- <a href="#" class="btn btn-view">
                                        <i class="bi bi-eye me-2"></i> View Details
                                    </a> -->
                                    <div class="d-flex align-items-center gap-3">
                                        <a href="<?= base_url('view/customer/product-detail.php?id=' . $product['product_id']) ?>" class="btn btn-view flex-grow-1"> <i class="bi bi-eye me-2"></i>View Details</a>
                                        <button class="btn btn-icon-fav">
                                            <i class="bi bi-bag-heart-fill"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <i class="bi bi-inbox"></i>
                    <h3>No Products Found</h3>
                    <p class="text-muted">Try adjusting your search or filter criteria</p>
                    <a href="<?= base_url('view/shop-listing.php') ?>" class="btn btn-search mt-3">
                        View All Products
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<?php
include '../view/customer/footer.php';

?>