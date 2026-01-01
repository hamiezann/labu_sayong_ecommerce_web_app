<?php
$user_id = $_SESSION['user_id'] ?? null;

// admin info
$q = "SELECT * FROM users WHERE id = '$user_id' LIMIT 1";
$e = mysqli_query($conn, $q);
$userInfo = mysqli_fetch_assoc($e);

if (!$e || mysqli_num_rows($e) == 0) {
    redirect(base_url('view/auth/login.php'));
}

getRoutePermission($_SERVER['PHP_SELF'], $userInfo['Role']);



// check image
if (!empty($userInfo['Image'])) {
    $imagePath = base_url($userInfo['Image']);
} else {
    $imagePath = base_url('assets/img/no_image.png');
}

?>
<!doctype html>
<html lang="en">

<head>
    <title><?= $title . ' | ' . $pageName ?></title>

    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes" />
    <meta name="color-scheme" content="light dark" />
    <meta name="theme-color" content="#007bff" media="(prefers-color-scheme: light)" />
    <meta name="theme-color" content="#1a1a1a" media="(prefers-color-scheme: dark)" />
    <meta name="title" content="<?= $title . ' | ' . $pageName ?>" />
    <meta name="author" content="ColorlibHQ" />
    <meta name="supported-color-schemes" content="light dark" />

    <!-- internal css -->
    <link rel="preload" href="<?= base_url('assets/css/adminlte.css') ?>" as="style" />
    <link rel="stylesheet" href="<?= base_url('assets/css/adminlte.css') ?>" />

    <!-- cdn css -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fontsource/source-sans-3@5.0.12/index.css" media="print" onload="this.media='all'" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.11.0/styles/overlayscrollbars.min.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/apexcharts@3.37.1/dist/apexcharts.css" />

    <!-- sweetalert -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- datatables -->
    <link rel="stylesheet" href="https://cdn.datatables.net/2.3.2/css/dataTables.bootstrap5.css" />
</head>

<body class="layout-fixed sidebar-expand-lg sidebar-open bg-body-tertiary">
    <div class="app-wrapper">
        <nav class="app-header navbar navbar-expand bg-body" style="background-color: #603F26!important;">
            <div class="container-fluid">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" data-lte-toggle="sidebar" style="color: white!important;" href="#" role="button">
                            <i class="bi bi-list"></i>
                        </a>
                    </li>
                    <li class="nav-item fw-semibold d-none d-md-block"><a href="#" style="color: white!important;" class="nav-link"><?= $title ?></a></li>
                </ul>
                <ul class="navbar-nav ms-auto">

                    <!-- fullscreen toggle -->
                    <li class="nav-item">
                        <a class="nav-link" href="#" data-lte-toggle="fullscreen" style="color: white!important;">
                            <i data-lte-icon="maximize" class="bi bi-arrows-fullscreen"></i>
                            <i data-lte-icon="minimize" class="bi bi-fullscreen-exit" style="display: none"></i>
                        </a>
                    </li>

                    <!-- user information -->
                    <li class="nav-item dropdown user-menu">
                        <a href="#" class="nav-link dropdown-toggle" style="color: white!important;" data-bs-toggle="dropdown">
                            <img
                                src="<?= $imagePath ?>"
                                class="user-image rounded-circle shadow"
                                alt="User Image" />
                            <span class="d-none d-md-inline"><?= strtr($userInfo['FullName'], 0, 20) ?></span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-end">
                            <li class="user-header text-center" style="background-color: #AF8F6F;">
                                <img
                                    src="<?= htmlspecialchars($imagePath) ?>"
                                    class="rounded-circle shadow mb-2"
                                    alt="User Image"
                                    width="80" height="80" />
                                <p class="mb-0 fw-semibold">
                                    <?= htmlspecialchars(strlen($userInfo['FullName']) > 20
                                        ? substr($userInfo['FullName'], 0, 20) . '...'
                                        : $userInfo['FullName']) ?>
                                </p>
                                <small>Created At: <?= date('d-m-Y', strtotime($userInfo['CreatedAt'])) ?></small>
                            </li>

                            <?php if ($userInfo['Role'] === 'staff'): ?>
                                <li class="user-footer text-center">
                                    <a href="<?= base_url('view/staff/staff-profile.php') ?>"
                                        class="btn btn-outline-success btn-flat w-75 mb-2">
                                        <i class="bi bi-person-circle me-1"></i> My Profile
                                    </a>
                                </li>
                            <?php endif; ?>

                            <li class="user-footer text-center">
                                <button type="button"
                                    data-bs-toggle="modal"
                                    data-bs-target="#modalSignOut"
                                    class="btn btn-outline-danger btn-flat w-75">
                                    <i class="bi bi-box-arrow-right me-1"></i> Sign Out
                                </button>
                            </li>
                        </ul>

                    </li>
                </ul>
            </div>
        </nav>