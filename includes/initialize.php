<?php

$title = "Labu Sayong WebApp";
$url = "http://localhost/labu-sayong-webapp/";


function base_url($path = '')
{
    global $url;
    return $url . ltrim($path, '/');
}

function redirect($path = '')
{
    header("Location: " . base_url($path));
    exit();
}

// use to escape output from XSS
function esc($output = '')
{
    return htmlspecialchars($output, ENT_QUOTES, 'UTF-8');
}

// use to sanitize input data from special characters
function sanitize($data = '')
{
    global $conn;
    return mysqli_real_escape_string($conn, $data);
}

// this encode and decode using base64
function encode($data = '')
{
    return base64_encode($data);
}

function decode($data = '')
{
    return base64_decode($data);
}

// use for session message and integarte with sweetalert2
function sessionMessage($status, $message)
{
    if ($status == 'success') {
        $_SESSION['message'] = [
            'icon'  => 'success',
            'title' => 'Success!',
        ];
    } else {
        $_SESSION['message'] = [
            'icon'  => 'error',
            'title' => 'Failed!',
        ];
    }

    $_SESSION['message']['text'] = $message;

    return $_SESSION['message'];
}
