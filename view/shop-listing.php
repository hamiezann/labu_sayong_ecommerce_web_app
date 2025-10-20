<?php
require_once '../includes/config.php';

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
$title = "Shop - Labu Sayong";

if (isset($_SESSION['id'])) {
    $userId = $_SESSION['id'];
    $userData = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM users WHERE id='$userId'"));
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title><?= $title ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Custom CSS -->
    <link href="../assets/css/product.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">


</head>

<body>

    <!-- NAVBAR -->
    <nav class="navbar navbar-expand-lg navbar-light sticky-top">
        <div class="container">
            <a class="navbar-brand" href="<?= base_url('index.php') ?>">
                <i class="bi bi-palette me-2"></i> Labu Sayong
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
                <ul class="navbar-nav align-items-center">
                    <li class="nav-item"><a class="nav-link" href="<?= base_url('index.php') ?>">Home</a></li>
                    <li class="nav-item"><a class="nav-link active" href="<?= base_url('shop.php') ?>">Products</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= base_url('index.php') ?>#about">About</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= base_url('index.php') ?>#contact">Contact</a></li>

                    <!-- Cart Icon -->
                    <li class="nav-item ms-3">
                        <a href="#" class="nav-link position-relative">
                            <i class="bi bi-bag" style="font-size: 1.3rem;"></i>
                            <span class="cart-badge">0</span>
                        </a>
                    </li>

                    <?php if (isset($_SESSION['user_id'])): ?>
                        <li class="nav-item ms-2">
                            <button class="btn btn-login" data-bs-toggle="modal" data-bs-target="#modalSignOut">
                                Logout
                            </button>
                        </li>
                    <?php else: ?>
                        <li class="nav-item ms-2">
                            <a href="<?= base_url('view/auth/login.php') ?>" class="btn btn-login">
                                Login
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

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
                        <a href="<?= base_url('shop.php') ?>" class="btn btn-reset w-100">
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
                    <select class="sort-select">
                        <option>Sort by: Latest</option>
                        <option>Price: Low to High</option>
                        <option>Price: High to Low</option>
                        <option>Name: A to Z</option>
                    </select>
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
                                        <a href="#" class="btn btn-view">
                                            <i class="bi bi-eye me-2"></i> View Details
                                        </a>
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
                        <a href="<?= base_url('shop.php') ?>" class="btn btn-search mt-3">
                            View All Products
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- FOOTER -->
    <footer>
        <div class="container">
            <div class="row">
                <div class="col-md-4 mb-4">
                    <h5><i class="bi bi-palette me-2"></i> Labu Sayong</h5>
                    <p class="small">Preserving the art of traditional Malaysian pottery and bringing authentic craftsmanship to your home.</p>
                </div>
                <div class="col-md-4 mb-4">
                    <h5>Quick Links</h5>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="<?= base_url('index.php') ?>">Home</a></li>
                        <li class="mb-2"><a href="<?= base_url('shop.php') ?>">Products</a></li>
                        <li class="mb-2"><a href="<?= base_url('index.php') ?>#about">About</a></li>
                        <li class="mb-2"><a href="<?= base_url('index.php') ?>#contact">Contact</a></li>
                    </ul>
                </div>
                <div class="col-md-4 mb-4">
                    <h5>Follow Us</h5>
                    <div class="d-flex">
                        <a href="#" class="social-btn"><i class="bi bi-facebook"></i></a>
                        <a href="#" class="social-btn"><i class="bi bi-instagram"></i></a>
                        <a href="#" class="social-btn"><i class="bi bi-whatsapp"></i></a>
                        <a href="#" class="social-btn"><i class="bi bi-twitter"></i></a>
                    </div>
                </div>
            </div>
            <hr style="border-color: rgba(255,255,255,0.1); margin: 2rem 0 1rem;">
            <div class="text-center">
                <p class="mb-0">&copy; <?= date('Y') ?> Labu Sayong. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        function setRange(min, max) {
            document.querySelector('input[name="min_price"]').value = min;
            document.querySelector('input[name="max_price"]').value = max;
            document.getElementById('filterForm').submit();
        }

        // Smooth scroll for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });
    </script>

</body>

</html>