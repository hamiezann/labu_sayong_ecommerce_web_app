<?php
require_once '../../includes/config.php';

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");
if (isset($_SESSION['user_id']) && isset($_SESSION['user_role'])) {
    switch ($_SESSION['user_role']) {
        case 'admin':
            header("Location: ../admin/dashboard.php");
            break;
        case 'staff':
            header("Location: ../staff/manage-product.php");
            break;
        default:
            header("Location: ../../index.php");
            break;
    }
    exit();
}

if (isset($_POST['sign-in'])) {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];

    $query = "SELECT * FROM users WHERE Email='$email' LIMIT 1";
    $result = mysqli_query($conn, $query);

    if ($result && mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);

        // Verify password (use password_hash on registration)
        if (password_verify($password, $user['Password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['FullName'];
            $_SESSION['user_role'] = $user['Role'];

            sessionMessage('success', 'Welcome back, ' . $user['FullName'] . '!');
            if ($user['Role'] == 'admin') {
                redirect('view/admin/dashboard.php');
            } else if ($user['Role'] == 'staff') {
                redirect('view/staff/manage-product.php');
            } else {
                redirect('index.php');
            }
        } else {
            sessionMessage('error', 'Invalid password.');
        }
    } else {
        sessionMessage('error', 'User not found.');
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title><?= $title ?> | Login</title>
    <link rel="stylesheet" href="<?= base_url('assets/css/adminlte.css') ?>">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body class="login-page bg-body-secondary">

    <?php require_once '../../includes/message.php'; ?>

    <div class="login-box">
        <div class="login-logo">
            <a href="<?= base_url('') ?>"><b>Labu Sayong</b></a>
        </div>

        <div class="card">
            <div class="card-body login-card-body">
                <p class="login-box-msg">Sign in to start your session</p>

                <form method="post">
                    <div class="input-group mb-3">
                        <input type="email" name="email" class="form-control" placeholder="Email" required>
                        <div class="input-group-text">
                            <span class="bi bi-envelope"></span>
                        </div>
                    </div>

                    <div class="input-group mb-3">
                        <input type="password" name="password" class="form-control" placeholder="Password" required>
                        <div class="input-group-text">
                            <span class="bi bi-lock-fill"></span>
                        </div>
                    </div>

                    <button type="submit" name="sign-in" class="btn btn-primary w-100">Login</button>
                </form>

                <p class="mt-3 text-center">Don't have an account?
                    <a href="<?= base_url('view/auth/register.php') ?>">Register here</a>
                </p>
            </div>
        </div>
    </div>
</body>

</html>