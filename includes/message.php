<?php
function message($icon, $title, $message)
{
    if (isset($_SESSION['message'])) {
        $icon = $_SESSION['message']['icon'];
        $title = $_SESSION['message']['title'];
        $message = $_SESSION['message']['text'];

        unset($_SESSION['message']);

        return "<script>
            Swal.fire({
                icon: '" . esc($icon) . "',
                title: '" . esc($title) . "',
                text: '" . esc($message) . "',
                timer: 3000,
                timerProgressBar: true,
                showConfirmButton: false
            });
        </script>";
    }
    return '';
}
