<?php
require_once '../../includes/config.php';

if (!isset($_GET['token']) || empty($_GET['token'])) {
    die("Invalid password reset link.");
}

$token = mysqli_real_escape_string($conn, $_GET['token']);

$result = mysqli_query($conn, "
    SELECT id 
    FROM users
    WHERE reset_token='$token'
     AND reset_expires > NOW()
    LIMIT 1
");

if (mysqli_num_rows($result) !== 1) {
    die("This password reset link is invalid or has expired.");
}

$user = mysqli_fetch_assoc($result);
$userId = $user['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (strlen($_POST['password']) < 8) {
        $_SESSION['error_message'] = "Password must be at least 8 characters.";
    } else {

        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

        mysqli_query($conn, "
            UPDATE users
            SET Password='$password',
                reset_token=NULL,
                reset_expires=NULL
            WHERE id='$userId'
        ");

        $_SESSION['success_message'] = "Password reset successful. Please login.";
        header("Location: login.php");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Reset Password | CRAFTEASE</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body style="background:#FAFAFA;">

    <div class="container d-flex align-items-center justify-content-center" style="min-height:100vh;">
        <div class="card shadow-sm" style="max-width:420px;width:100%;">
            <div class="card-body p-4">

                <h3 class="mb-3 fw-bold">Reset Password 🔐</h3>
                <p class="text-muted mb-4">Enter your new password below</p>

                <?php if (isset($_SESSION['error_message'])): ?>
                    <div class="alert alert-danger">
                        <?= $_SESSION['error_message'];
                        unset($_SESSION['error_message']); ?>
                    </div>
                <?php endif; ?>

                <form method="post">
                    <div class="mb-3">
                        <label class="form-label">New Password</label>
                        <input type="password"
                            name="password"
                            class="form-control"
                            required
                            minlength="8">
                    </div>

                    <button type="submit"
                        class="btn btn-dark w-100">
                        Reset Password
                    </button>
                </form>

            </div>
        </div>
    </div>

</body>

</html>