<?php
// 1. MUST START SESSION AT THE TOP
session_start();

require_once '../../includes/config.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Ensure these paths are correct relative to this file
require '../../vendor/phpmailer/phpmailer/src/Exception.php';
require '../../vendor/phpmailer/phpmailer/src/PHPMailer.php';
require '../../vendor/phpmailer/phpmailer/src/SMTP.php';

if (isset($_POST['submit'])) {
    $email = $_POST['email'];

    // 2. USE PREPARED STATEMENTS (SAFER)
    $stmt = $conn->prepare("SELECT id, FullName FROM users WHERE Email=? AND acc_status=1 LIMIT 1");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        $token = bin2hex(random_bytes(32));
        $expires = date("Y-m-d H:i:s", strtotime('+1 hour'));

        // Update token using prepared statement
        $updateStmt = $conn->prepare("UPDATE users SET reset_token=?, reset_expires=? WHERE Email=?");
        $updateStmt->bind_param("sss", $token, $expires, $email);
        $updateStmt->execute();

        // $resetLink = base_url("view/auth/reset-password.php?token=$token");
        $resetLink = base_url(
            "view/auth/reset-password.php?token=" . urlencode($token)
        );
        // ✉️ SEND EMAIL
        try {
            $mail = new PHPMailer(true);
            $mail->isSMTP();
            $mail->Host       = SMTP_HOST;
            $mail->SMTPAuth   = true;
            $mail->Username   = SMTP_USER;
            $mail->Password   = SMTP_PASS;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = SMTP_PORT;

            $mail->setFrom(SMTP_FROM, SMTP_NAME);
            $mail->addAddress($email, $user['FullName']);

            $mail->isHTML(true);
            $mail->Subject = 'Reset Your Password - CRAFTEASE';
            $mail->Body    = "
                <div style='font-family: Arial; padding:20px;'>
                    <h2>Password Reset Request</h2>
                    <p>Hello {$user['FullName']},</p>
                    <p>You requested to reset your password.</p>
                    <p><a href='{$resetLink}' style='display:inline-block; padding:12px 20px; background:#4F46E5; color:#fff; text-decoration:none; border-radius:6px;'>Reset Password</a></p>
                    <p>This link will expire in 1 hour.</p>
                    <hr>
                    <small>If you did not request this, please ignore this email.</small>
                </div>";

            $mail->send();

            $_SESSION['success_message'] = "Password reset link has been sent to your email.";
            header("Location: " . base_url('view/auth/login.php'));
            exit();
        } catch (Exception $e) {
            $_SESSION['error_message'] = "Mailer Error: " . $mail->ErrorInfo;
        }
    } else {
        // Security tip: Use a vague message so hackers don't know if email exists
        $_SESSION['error_message'] = "If this email exists in our system, a reset link will be sent.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Forgot Password | CRAFTEASE</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body style="background:#FAFAFA;">

    <?php
    if (isset($_SESSION['success_message'])) {
        echo "<script>
            Swal.fire({
                icon: 'success',
                title: 'Email Sent',
                text: " . json_encode($_SESSION['success_message']) . ",
                confirmButtonColor: '#4F46E5'
            });
        </script>";
        unset($_SESSION['success_message']);
    }

    if (isset($_SESSION['error_message'])) {
        echo "<script>
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: " . json_encode($_SESSION['error_message']) . ",
                confirmButtonColor: '#4F46E5'
            });
        </script>";
        unset($_SESSION['error_message']);
    }
    ?>

    <div class="container-fluid" style="min-height:100vh; display:flex;">
        <div class="row w-100 m-0">
            <div class="col-lg-5 col-md-6 d-flex align-items-center justify-content-center">
                <div style="width:100%; max-width:420px;">
                    <div class="mb-5">
                        <h1 style="font-size:2.5rem;font-weight:700;color:#111827;">Forgot Password 🔐</h1>
                        <p style="color:#6B7280;">We’ll send you a reset link</p>
                    </div>

                    <form method="post" action="">
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <div class="position-relative">
                                <span style="position:absolute;left:1rem;top:50%;transform:translateY(-50%);color:#9CA3AF;">
                                    <i class="bi bi-envelope"></i>
                                </span>
                                <input type="email" name="email" required class="form-control" style="padding-left:3rem;border-radius:8px;">
                            </div>
                        </div>

                        <button type="submit" name="submit" class="btn w-100 text-white" style="background:#2C2C2C;padding:0.875rem;border-radius:8px;">
                            Send Reset Link
                        </button>

                        <p class="text-center mt-4">
                            <a href="<?= base_url('view/auth/login.php') ?>" style="color:#4F46E5;text-decoration:none;font-weight:600;">
                                Back to login
                            </a>
                        </p>
                    </form>
                </div>
            </div>

            <div class="col-lg-7 col-md-6 d-none d-md-flex" style="background:url('<?= base_url('assets/img/bg3.jpg') ?>') no-repeat center/cover; position:relative;">
                <div style="position:absolute;inset:0;background:rgba(0,0,0,.35);"></div>
                <div class="d-flex align-items-center justify-content-center w-100" style="z-index:2;">
                    <h2 style="color:#fff;font-size:2.8rem;font-weight:800;">CRAFTEASE</h2>
                </div>
            </div>
        </div>
    </div>
</body>

</html>