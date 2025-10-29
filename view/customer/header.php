<?php
require_once __DIR__ . '/../../includes/config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$user_id = $_SESSION['user_id'] ?? null;
$cart_count = isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0;

if ($user_id) {
    $q = "SELECT * FROM users WHERE id = '$user_id' LIMIT 1";
    $e = mysqli_query($conn, $q);
    $userInfo = mysqli_fetch_assoc($e);

    if (!$e || mysqli_num_rows($e) == 0) {
        redirect(base_url('view/auth/login.php'));
        exit();
    }

    getRoutePermission($_SERVER['PHP_SELF'], $userInfo['Role']);

    $imagePath = !empty($userInfo['Image'])
        ? base_url($userInfo['Image'])
        : base_url('assets/img/no_image.png');
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title><?= $title ?? 'Labu Sayong' ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Custom CSS -->
    <link href="<?= base_url('assets/css/product.css') ?>" rel="stylesheet">
    <link href="<?= base_url('assets/css/client.css') ?>" rel="stylesheet">
</head>

<body style="font-family: 'Poppins', sans-serif;">



    <!-- NAVBAR -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm sticky-top">
        <div class="container">
            <a class="navbar-brand fw-semibold text-primary" href="<?= base_url('index.php') ?>">
                <i class="bi bi-palette me-2"></i> Labu Sayong
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
                <ul class="navbar-nav align-items-center">
                    <li class="nav-item"><a class="nav-link" href="<?= base_url('index.php') ?>">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= base_url('view/shop-listing.php') ?>">Products</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= base_url('index.php#about') ?>">About</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= base_url('index.php#contact') ?>">Contact</a></li>

                    <!-- Cart -->
                    <li class="nav-item ms-3 position-relative">
                        <a href="<?= base_url('view/customer/cart.php') ?>" class="nav-link">
                            <i class="bi bi-bag" style="font-size: 1.3rem;"></i>
                            <span class="cart-badge"><?= $cart_count ?></span>
                        </a>
                    </li>

                    <?php if (isset($_SESSION['user_id'])): ?>
                        <!-- USER DROPDOWN -->
                        <li class="nav-item dropdown ms-3">
                            <a class="nav-link dropdown-toggle d-flex align-items-center" href="#UserImage" id="userDropdown"
                                role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <img src="<?= $imagePath ?>" alt="User Image" class="rounded-circle me-2" width="32" height="32">
                                <span class="fw-semibold"><?= htmlspecialchars(explode(' ', $userInfo['FullName'])[0]) ?></span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end shadow" aria-labelledby="userDropdown">
                                <li>
                                    <a class="dropdown-item" href="<?= base_url('view/customer/my-profile.php') ?>">
                                        <i class="bi bi-person-circle me-2 text-primary"></i> My Profile
                                    </a>
                                </li>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li>
                                    <button class="dropdown-item text-danger" data-bs-toggle="modal" data-bs-target="#modalSignOut">
                                        <i class="bi bi-box-arrow-right me-2"></i> Logout
                                    </button>
                                </li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item ms-3">
                            <a href="<?= base_url('view/auth/login.php') ?>" class="btn btn-outline-primary px-3">
                                <i class="bi bi-person"></i> Login
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Alerts -->
    <?php if (isset($_SESSION['success_message'])) : ?>
        <div class="alert alert-success"><?= $_SESSION['success_message'];
                                            unset($_SESSION['success_message']); ?></div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error_message'])) : ?>
        <div class="alert alert-danger"><?= $_SESSION['error_message'];
                                        unset($_SESSION['error_message']); ?></div>
    <?php endif; ?>

    <!-- LOGOUT CONFIRMATION MODAL -->
    <div class="modal fade" id="modalSignOut" tabindex="-1" aria-labelledby="modalSignOutLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="modalSignOutLabel">
                        <i class="bi bi-box-arrow-right me-2"></i> Confirm Logout
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center">
                    <p class="fs-5 mb-0">Are you sure you want to log out?</p>
                </div>
                <div class="modal-footer justify-content-center">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <a href="<?= base_url('view/auth/logout.php') ?>" class="btn btn-danger px-4">
                        <i class="bi bi-box-arrow-right me-1"></i> Logout
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>