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

function getRoutePermission($url, $role)
{
    // Normalize for consistency
    $url = strtolower(trim($url));
    $role = strtolower(trim($role));

    // Define allowed routes per role
    $permissions = [
        'admin' => [
            'admin/dashboard.php',
            'admin/manage-staff.php',
            'admin/manage-customer.php',
            'staff/manage-product.php',
            'staff/chat-list.php',
            'staff/order-list.php',
            // 'staff/manage-product.php',

        ],
        'staff' => [
            'staff/manage-product.php',
            'staff/chat-list.php',
            'staff/order-list.php',
            'admin/manage-customer.php',
            'staff/staff-profile.php'
        ],
        'customer' => [
            'index.php',
            'shop-listing.php',
            'cart.php',
            'customer/my-profile.php',
            'product-detail.php'
        ],
    ];

    // If role not defined, treat as guest (no permission)
    if (!isset($permissions[$role])) {
        header("Location: " . base_url('view/error404.php'));
        exit();
    }

    // Check if the requested URL matches any allowed page for that role
    $allowed = false;
    foreach ($permissions[$role] as $allowedPage) {
        if (strpos($url, strtolower($allowedPage)) !== false) {
            $allowed = true;
            break;
        }
    }

    // If no match found → redirect to 404 or unauthorized page
    if (!$allowed) {
        header("Location: " . base_url('view/error404.php'));
        exit();
    }
}
