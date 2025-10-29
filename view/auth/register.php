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
        sessionMessage('error', 'Email already registered.');
    } else {
        $query = "INSERT INTO users (FullName, Email, Password, Role) 
                  VALUES ('$fullname', '$email', '$password', '$role')";
        if (mysqli_query($conn, $query)) {
            sessionMessage('success', 'Account created successfully! Please login.');
            redirect('view/auth/login.php');
        } else {
            sessionMessage('error', 'Registration failed.');
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title><?= $title ?> | Register</title>
    <link rel="stylesheet" href="<?= base_url('assets/css/adminlte.css') ?>">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body class="register-page bg-body-secondary">

    <?php require_once '../../includes/message.php'; ?>

    <div class="register-box">
        <div class="register-logo">
            <a href="<?= base_url('') ?>"><b>Labu Sayong</b></a>
        </div>

        <div class="card">
            <div class="card-body register-card-body">
                <p class="login-box-msg">Register a new membership</p>

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

                    <button type="submit" name="register" class="btn btn-success w-100">Register</button>
                </form>

                <p class="mt-3 text-center">Already have an account?
                    <a href="<?= base_url('view/auth/login.php') ?>">Login here</a>
                </p>
            </div>
        </div>
    </div>
</body>

</html>