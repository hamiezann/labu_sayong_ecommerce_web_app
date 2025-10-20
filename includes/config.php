<?php
// define('ROOT_PATH', dirname(__DIR__));
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$hostname = "localhost";
$username = "root";
$password = "";
$database = "labu_sayong_db";

$conn = mysqli_connect($hostname, $username, $password, $database);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

require_once 'initialize.php';
require_once 'function.php';
