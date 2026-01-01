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

        if (password_verify($password, $user['Password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['FullName'];
            $_SESSION['user_role'] = $user['Role'];

            if ($user['changeDefPass'] == 0) {
                $_SESSION['warning_message'] = "Welcome back, don't forget to change your password for your account safety!";
            } else {
                $_SESSION['success_message'] = "Welcome back " . htmlspecialchars($user['FullName']) . "!";
            }

            header("Location: register.php");
            if ($user['Role'] == 'admin') {
                redirect('view/admin/dashboard.php');
            } else if ($user['Role'] == 'staff') {
                redirect('view/admin/manage-customer.php');
            } else {
                redirect('index.php');
            }
        } else {
            $_SESSION['error_message'] = "Invalid password. Please try again.";
        }
    } else {
        $_SESSION['error_message'] = "No account found with this email address.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?> | Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body style="margin: 0; padding: 0; overflow-x: hidden; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;">

    <?php
    if (isset($_SESSION['success_message'])) {
        $msg = $_SESSION['success_message'];
        unset($_SESSION['success_message']);
        echo "<script>
    document.addEventListener('DOMContentLoaded', function() {
      Swal.fire({
        icon: 'success',
        title: 'Success',
        text: " . json_encode($msg) . ",
        confirmButtonColor: '#4F46E5'
      }).then(() => {
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
        title: 'Oops...',
        text: " . json_encode($msg) . ",
        confirmButtonColor: '#4F46E5'
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
        icon: 'warning',
        title: 'Important Notice',
        text: " . json_encode($msg) . ",
        confirmButtonColor: '#ffc107'
      });
    });
    </script>";
    }
    ?>

    <div class="container-fluid" style="min-height: 100vh; display: flex;">
        <div class="row w-100 m-0">

            <!-- Left Side - Form -->
            <div class="col-lg-5 col-md-6 d-flex align-items-center justify-content-center" style="background: #FAFAFA; padding: 3rem 2rem; position: relative;">

                <div style="width: 100%; max-width: 420px;">

                    <!-- Header -->
                    <div class="mb-5">
                        <h1 class="mb-2" style="font-size: 2.5rem; font-weight: 700; color: #111827;">Welcome back 👋</h1>
                        <p class="mb-0" style="color: #6B7280; font-size: 1rem;">Please enter your details</p>
                    </div>

                    <!-- Form -->
                    <form method="post">

                        <!-- Email Input -->
                        <div class="mb-3">
                            <label for="email" class="form-label" style="color: #374151; font-weight: 500; font-size: 0.95rem; margin-bottom: 0.5rem;">Email</label>
                            <div class="position-relative">
                                <span style="position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: #9CA3AF;">
                                    <i class="bi bi-envelope" style="font-size: 1.1rem;"></i>
                                </span>
                                <input type="email" name="email" id="email" class="form-control" placeholder="Enter your email" required style="padding: 0.75rem 1rem 0.75rem 3rem; border: 1px solid #E5E7EB; border-radius: 8px; font-size: 1rem; transition: all 0.3s;" onfocus="this.style.borderColor='#4F46E5'; this.style.boxShadow='0 0 0 3px rgba(79, 70, 229, 0.1)'" onblur="this.style.borderColor='#E5E7EB'; this.style.boxShadow='none'">
                            </div>
                        </div>

                        <!-- Password Input -->
                        <div class="mb-3">
                            <label for="password" class="form-label" style="color: #374151; font-weight: 500; font-size: 0.95rem; margin-bottom: 0.5rem;">Password</label>
                            <div class="position-relative">
                                <span style="position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: #9CA3AF;">
                                    <i class="bi bi-lock-fill" style="font-size: 1.1rem;"></i>
                                </span>
                                <input type="password" name="password" id="password" class="form-control" placeholder="Enter your password" required style="padding: 0.75rem 3rem 0.75rem 3rem; border: 1px solid #E5E7EB; border-radius: 8px; font-size: 1rem; transition: all 0.3s;" onfocus="this.style.borderColor='#4F46E5'; this.style.boxShadow='0 0 0 3px rgba(79, 70, 229, 0.1)'" onblur="this.style.borderColor='#E5E7EB'; this.style.boxShadow='none'">
                                <button class="btn" type="button" onclick="togglePassword()" style="position: absolute; right: 0.5rem; top: 50%; transform: translateY(-50%); border: none; background: transparent; padding: 0.5rem; color: #9CA3AF;">
                                    <i class="bi bi-eye" id="toggleIcon" style="font-size: 1.1rem;"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Login Button -->
                        <button type="submit" name="sign-in" class="btn w-100 text-white mb-3" style="background: #2C2C2C; border: none; padding: 0.875rem; border-radius: 8px; font-weight: 600; font-size: 1rem; transition: all 0.3s;" onmouseover="this.style.background='#1F1F1F'" onmouseout="this.style.background='#2C2C2C'">
                            Log In
                        </button>



                        <!-- Sign Up Link -->
                        <div class="text-center">
                            <p class="mb-0" style="color: #6B7280; font-size: 0.95rem;">
                                Don't have an account?
                                <a href="<?= base_url('view/auth/register.php') ?>" style="color: #4F46E5; text-decoration: none; font-weight: 600;">Sign Up</a>
                            </p>
                            <p class="mb-0" style="color: #6B7280; font-size: 0.95rem;">
                                Go Back
                                <a href="<?= base_url('') ?>" style="color: #4F46E5; text-decoration: none; font-weight: 600;">Home</a>
                            </p>
                        </div>
                        <p class="text-center mt-3">
                            <a style="color: #4F46E5; text-decoration: none; font-weight: 600;" href="<?= base_url('view/auth/forgot-password.php') ?>">
                                Forgot password?
                            </a>
                        </p>

                    </form>
                </div>
            </div>
            <!-- Right Side - Background Image -->
            <div class="col-lg-7 col-md-6 d-none d-md-flex" style="background: url('<?= base_url('assets/img/bg3.jpg') ?>') no-repeat center center/cover;position: relative; min-height: 100vh;">
                <div style=" position: absolute; inset: 0;background: rgba(0,0,0,0.35);"></div>
                <div class="d-flex flex-column align-items-center justify-content-center w-100"
                    style="z-index: 2; text-align: center; padding: 3rem;">
                    <h2 style="color: #ffffff;font-size: 2.8rem; font-weight: 800; letter-spacing: 2px;text-shadow: 0 4px 20px rgba(0,0,0,0.5);">
                        CRAFTEASE
                    </h2>
                </div>
            </div>
        </div>
    </div>

    <!-- CSS -->
    <style>
        .form-control:focus {
            outline: none;
        }

        .form-check-input:checked {
            background-color: #4F46E5;
            border-color: #4F46E5;
        }

        .form-check-input:focus {
            border-color: #4F46E5;
            box-shadow: 0 0 0 0.2rem rgba(79, 70, 229, 0.25);
        }

        @media (max-width: 768px) {
            .col-lg-5 {
                padding: 2rem 1.5rem !important;
            }
        }
    </style>

    <!-- Toggle Password Script -->
    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.getElementById('toggleIcon');

            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.classList.remove('bi-eye');
                toggleIcon.classList.add('bi-eye-slash');
            } else {
                passwordInput.type = 'password';
                toggleIcon.classList.remove('bi-eye-slash');
                toggleIcon.classList.add('bi-eye');
            }
        }
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>