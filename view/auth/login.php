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

    $query = "SELECT * FROM users WHERE Email='$email' AND acc_status=1 LIMIT 1";
    $result = mysqli_query($conn, $query);

    if ($result && mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);

        // Verify password (use password_hash on registration)
        if (password_verify($password, $user['Password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['FullName'];
            $_SESSION['user_role'] = $user['Role'];

            // sessionMessage('success', 'Welcome back, ' . $user['FullName'] . '!');
            if ($user['changeDefPass'] == 0) {
                $_SESSION['warning_message'] = "Welcome back, don't forget to change your password for your account safety!";
            } else {
                $_SESSION['success_message'] = "Welcome back " . htmlspecialchars($user['FullName']) . "!";
            }
            // var_dump($_SESSION);
            // exit();

            header("Location: register.php");
            if ($user['Role'] == 'admin') {
                redirect('view/admin/dashboard.php');
            } else if ($user['Role'] == 'staff') {
                redirect('view/admin/manage-customer.php');
            } else {
                redirect('index.php');
            }
        } else {
            // sessionMessage('error', 'Invalid password.');
            $_SESSION['error_message'] = "❌ Invalid password.";
        }
    } else {
        // sessionMessage('error', 'User not found.');
        $_SESSION['error_message'] = "❌ User not found.";
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

    <?php
    // After session_start() and including SweetAlert JS in the page head
    if (isset($_SESSION['success_message'])) {
        $msg = $_SESSION['success_message'];
        unset($_SESSION['success_message']);
        echo "<script>
    document.addEventListener('DOMContentLoaded', function() {
      Swal.fire({
        icon: 'success',
        title: 'Success',
        text: " . json_encode($msg) . ",
        confirmButtonColor: '#198754'
      }).then(() => {
        // optional: auto-focus login email input
        const el = document.querySelector('input[name=\"email\"]');
        if (el) el.focus();
      });
    });
    </script>";
    }

    if (isset($_SESSION['error_message'])) {
        $msg = $_SESSION['error_message'];
        unset($_SESSION['error_message']);
        echo "<script>
    document.addEventListener('DOMContentLoaded', function() {
      Swal.fire({
        icon: 'error',
        title: 'Error',
        text: " . json_encode($msg) . ",
        confirmButtonColor: '#dc3545'
      });
    });
    </script>";
    }

    if (isset($_SESSION['warning_message'])) {
        $msg = $_SESSION['warning_message'];
        unset($_SESSION['warning_message']);
        echo "<script>
    document.addEventListener('DOMContentLoaded', function() {
      Swal.fire({
        icon: 'error',
        title: 'Error',
        text: " . json_encode($msg) . ",
        confirmButtonColor: '#c3e73fff'
      });
    });
    </script>";
    }
    ?>


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