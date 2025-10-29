<?php
http_response_code(404);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - Page Not Found</title>

    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f7f9fc;
            color: #333;
            margin: 0;
            padding: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            flex-direction: column;
            text-align: center;
        }

        h1 {
            font-size: 6rem;
            margin: 0;
            color: #007bff;
        }

        h2 {
            margin-top: 0;
            font-weight: 400;
            color: #555;
        }

        p {
            color: #777;
            max-width: 400px;
            margin: 10px auto 30px;
        }

        a {
            display: inline-block;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            padding: 10px 25px;
            border-radius: 5px;
            transition: background 0.3s;
        }

        a:hover {
            background-color: #0056b3;
        }

        .emoji {
            font-size: 4rem;
            margin-bottom: 20px;
        }
    </style>
</head>

<body>
    <div>
        <div class="emoji">😕</div>
        <h1>404</h1>
        <h2>Page Not Found</h2>
        <p>Sorry, the page you’re looking for doesn’t exist or may have been moved.</p>
        <a href="<?= base_url('index.php'); ?>">Go Home</a>
    </div>
</body>

</html>