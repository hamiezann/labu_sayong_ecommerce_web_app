<?php
date_default_timezone_set('Asia/Kuala_Lumpur');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$hostname = "localhost";
$username = "root";
$password = "";
$database = "labu_sayong_db";

$conn = mysqli_connect($hostname, $username, $password, $database);
mysqli_query($conn, "SET time_zone = '+08:00'");
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// email config
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_USER', 'typewarrior2@gmail.com');   // YOUR GMAIL
define('SMTP_PASS', 'oekr rqll shbo vcrt');      // APP PASSWORD
define('SMTP_PORT', 587);
define('SMTP_FROM', 'typewarrior2@gmail.com');   // SAME AS USER
define('SMTP_NAME', 'CRAFTEASE Support');


require_once 'initialize.php';
require_once 'function.php';
