<?php
include '../../includes/config.php';

session_start();

//  Must be logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

//  Only admin can delete
$user_id = $_SESSION['user_id'];
$userQuery = mysqli_query($conn, "SELECT Role FROM users WHERE id = '$user_id'");
$user = mysqli_fetch_assoc($userQuery);

if (!$user || $user['Role'] !== 'admin') {
    die('Unauthorized access.');
}

//  Validate session_id
if (!isset($_GET['session_id']) || empty($_GET['session_id'])) {
    die('Invalid chat session.');
}

$session_id = mysqli_real_escape_string($conn, $_GET['session_id']);

// Use transaction for safety
mysqli_begin_transaction($conn);

try {
    // delete chat messages
    mysqli_query($conn, "
        DELETE FROM chats 
        WHERE session_id = '$session_id'
    ");

    //  Delete chat session
    mysqli_query($conn, "
        DELETE FROM chat_sessions 
        WHERE session_id = '$session_id'
    ");
    mysqli_commit($conn);
    $_SESSION['success_message'] = "🗑️ Chat deleted succesfully!";
    header("Location: chat-list.php");
    exit();
    // header("Location: chat-list.php?deleted=1");
    // exit();
} catch (Exception $e) {
    mysqli_rollback($conn);
    $_SESSION['error_message'] = "Chat deletion falied!";
    header("Location: chat-list.php");
    die('Failed to delete chat.');
}
