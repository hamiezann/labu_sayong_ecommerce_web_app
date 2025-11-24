<?php
include '../../includes/config.php';
if (!isset($_SESSION['user_id'])) {
    echo "ERROR: Not logged in";
    exit();
}

$admin_id = $_SESSION['user_id'];


$userQuery = mysqli_query($conn, "SELECT Role FROM users WHERE id = '$admin_id'");
$userInfo = mysqli_fetch_assoc($userQuery);
if ($userInfo['Role'] !== 'admin') {
    echo "ERROR: Unauthorized";
    exit();
}
if (!isset($_POST['session_id']) || !isset($_POST['staff_id'])) {
    echo "ERROR: Missing parameters";
    exit();
}

$session_id = mysqli_real_escape_string($conn, $_POST['session_id']);
$staff_id = mysqli_real_escape_string($conn, $_POST['staff_id']);

if ($staff_id === "") {
    $update = mysqli_query($conn, "
        UPDATE chat_sessions 
        SET assigned_staff_id = NULL 
        WHERE session_id = '$session_id'
    ");
} else {
    $staffCheck = mysqli_query($conn, "
        SELECT id FROM users 
        WHERE id = '$staff_id' AND Role IN ('staff','admin')
    ");
    if (mysqli_num_rows($staffCheck) === 0) {
        echo "ERROR: Invalid staff ID";
        exit();
    }

    $update = mysqli_query($conn, "
        UPDATE chat_sessions 
        SET assigned_staff_id = '$staff_id'
        WHERE session_id = '$session_id'
    ");
}

if ($update) {
    echo "OK";
} else {
    echo "ERROR: Database update failed";
}
