<?php
require_once '../../includes/config.php';
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");
if (isset($_SESSION['user_id'])) {
    header("Location: ../admin/dashboard.php");
    exit();
}
if (isset($_POST['register'])) {
    $fullname = mysqli_real_escape_string($conn, $_POST['fullname']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = 'customer';

    $check = mysqli_query($conn, "SELECT * FROM users WHERE Email='$email'");
    if (mysqli_num_rows($check) > 0) {
        // sessionMessage('error', 'Email already registered.');
        $_SESSION['error_message'] = "❌ Email already registered.";
        header("Location: register.php");
        exit();
    } else {
        $query = "INSERT INTO users (FullName, Email, Password, Role) 
                  VALUES ('$fullname', '$email', '$password', '$role')";
        if (mysqli_query($conn, $query)) {
            // sessionMessage('success', 'Account created successfully! Please login.');
            $_SESSION['success_message'] = "✅ Account created successfully! Please login.";
            header("Location: login.php");
        } else {
            // sessionMessage('error', 'Registration failed.');
            $_SESSION['error_message'] = "❌ Registration failed..";
            header("Location: register.php");
        }
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title><?= $title ?> | Register</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body style="margin: 0; padding: 0; overflow-x: hidden; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;">



    <!-- Alert message -->
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
        <!-- <div class="register-logo">
            <a href="<?= base_url('') ?>"><b>Labu Sayong</b></a>
        </div> -->
        <div class="row w-100 m-0">
            <div class="col-lg-5 col-md-6 d-flex align-items-center justify-content-center" style="background: #FAFAFA; padding: 3rem 2rem; position: relative;">
                <div style="width: 100%; max-width: 420px;">
                    <!-- <p class="login-box-msg">Register a new membership</p> -->
                    <div class="mb-5">
                        <h1 class="mb-2" style="font-size: 2.5rem; font-weight: 700; color: #111827;">New here? 👋</h1>
                        <p class="mb-0" style="color: #6B7280; font-size: 1rem;">Register a new membership</p>
                    </div>

                    <form method="post">
                        <div class="input-group mb-3">
                            <input type="text" name="fullname" class="form-control" placeholder="Full Name" required>
                            <div class="input-group-text"><span class="bi bi-person"></span></div>
                        </div>

                        <div class="input-group mb-3">
                            <input type="email" name="email" class="form-control" placeholder="Email" required>
                            <div class="input-group-text"><span class="bi bi-envelope"></span></div>
                        </div>

                        <div class="input-group mb-3">
                            <input type="password" name="password" class="form-control" placeholder="Password" required>
                            <div class="input-group-text"><span class="bi bi-lock-fill"></span></div>
                        </div>

                        <!-- <button type="submit" name="register" class="btn btn-success w-100"> -->
                        <button type="submit" name="register" class="btn w-100 text-white mb-3" style="background: #2C2C2C; border: none; padding: 0.875rem; border-radius: 8px; font-weight: 600; font-size: 1rem; transition: all 0.3s;" onmouseover="this.style.background='#1F1F1F'" onmouseout="this.style.background='#2C2C2C'">
                            Register
                        </button>

                        <!-- login Link -->
                        <div class="text-center">
                            <p class="mb-0" style="color: #6B7280; font-size: 0.95rem;">
                                Already have an account?
                                <a href="<?= base_url('view/auth/login.php') ?>" style="color: #4F46E5; text-decoration: none; font-weight: 600;">Sign In</a>
                            </p>
                            <p class="mb-0" style="color: #6B7280; font-size: 0.95rem;">
                                Go Back
                                <a href="<?= base_url('') ?>" style="color: #4F46E5; text-decoration: none; font-weight: 600;">Home</a>
                            </p>
                        </div>
                    </form>

                    <!-- <p class="mt-3 text-center">Already have an account?
                        <a href="<?= base_url('view/auth/login.php') ?>">Login here</a>
                    </p> -->
                </div>
            </div>

            <div class="col-lg-7 col-md-6 d-none d-md-flex align-items-center justify-content-center" style="background: linear-gradient(135deg, #6366F1 0%, #8B5CF6 100%); padding: 3rem; position: relative; overflow: hidden;">

                <!-- 3D Illustration Placeholder -->
                <div style="width: 100%; max-width: 600px; text-align: center; position: relative; z-index: 2;">
                    <!-- <div style="font-size: 15rem; line-height: 1; margin-bottom: 2rem; filter: drop-shadow(0 20px 40px rgba(0,0,0,0.3));">
                        🔐
                    </div> -->
                    <h2 style="color: white; font-size: 2.5rem; font-weight: 700; margin-bottom: 1rem; text-shadow: 0 2px 20px rgba(0,0,0,0.2);">Labu Sayong</h2>
                    <!-- <p style="color: rgba(255,255,255,0.9); font-size: 1.2rem; line-height: 1.6;">Your data is protected with enterprise-grade encryption and security protocols.</p> -->
                </div>

                <!-- Decorative Elements -->
                <div style="position: absolute; top: 10%; left: 5%; width: 100px; height: 100px; background: rgba(255,255,255,0.1); border-radius: 20px; transform: rotate(45deg);"></div>
                <div style="position: absolute; bottom: 15%; right: 8%; width: 150px; height: 150px; background: rgba(255,255,255,0.08); border-radius: 30px; transform: rotate(-15deg);"></div>
                <div style="position: absolute; top: 50%; right: 15%; width: 60px; height: 60px; background: rgba(255,255,255,0.12); border-radius: 50%;"></div>
                <div style="position: absolute; bottom: 30%; left: 10%; width: 80px; height: 80px; border: 3px solid rgba(255,255,255,0.2); border-radius: 15px; transform: rotate(25deg);"></div>
            </div>
        </div>
    </div>

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
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>