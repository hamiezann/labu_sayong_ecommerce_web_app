<?php
require_once '../../includes/config.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$currentRole = $_SESSION['user_role'] === 'staff';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current_password = mysqli_real_escape_string($conn, $_POST['current_password']);
    $new_password = mysqli_real_escape_string($conn, $_POST['new_password']);
    $confirm_password = mysqli_real_escape_string($conn, $_POST['confirm_password']);

    // Validate inputs
    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $_SESSION['error_message'] = "⚠️ All password fields are required.";
        header("Location: ../customer/my-profile.php");
        exit();
    }

    if ($new_password !== $confirm_password) {
        $_SESSION['error_message'] = "❌ New password and confirm password do not match.";
        if ($currentRole) {
            header("Location: ../staff/staff-profile.php");
        } else {
            header("Location: ../customer/my-profile.php");
        }
        exit();
    }

    // Check current password
    $result = mysqli_query($conn, "SELECT Password FROM users WHERE id='$user_id'");
    $user = mysqli_fetch_assoc($result);

    if (!$user || !password_verify($current_password, $user['Password'])) {
        $_SESSION['error_message'] = "❌ Current password is incorrect.";
        if ($currentRole) {
            header("Location: ../staff/staff-profile.php");
        } else {
            header("Location: ../customer/my-profile.php");
        }
        exit();
    }

    // Hash and update new password
    $hashedPassword = password_hash($new_password, PASSWORD_BCRYPT);

    $update = mysqli_query($conn, "UPDATE users SET Password='$hashedPassword', UpdatedAt=NOW(), changeDefPass =1 WHERE id='$user_id'");

    if ($update) {
        mysqli_query($conn, "INSERT INTO logs (user_id, action) VALUES ('$user_id', 'Updated password')");
        $_SESSION['success_message'] = "✅ Password updated successfully!";
    } else {
        $_SESSION['error_message'] = "❌ Failed to update password. Please try again.";
    }

    if ($currentRole) {
        header("Location: ../staff/staff-profile.php");
    } else {
        header("Location: ../customer/my-profile.php");
    }
    exit();
}
