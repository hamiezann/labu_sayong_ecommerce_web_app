<?php
function addLog($conn, $user_id, $action)
{
    $stmt = $conn->prepare("INSERT INTO logs (user_id, action) VALUES (?, ?)");
    $stmt->bind_param("is", $user_id, $action);
    $stmt->execute();
    $stmt->close();
}
