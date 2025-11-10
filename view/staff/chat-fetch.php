<?php
require_once '../../includes/config.php';


$session_id = intval($_GET['session_id'] ?? 0);
if ($session_id <= 0) exit('Invalid session');

$user_id = $_SESSION['user_id'];

$query = mysqli_query($conn, "
    SELECT c.*, u.FullName AS sender_name
    FROM chats c
    JOIN users u ON c.sender_id = u.id
    WHERE c.session_id = '$session_id'
    ORDER BY c.created_at ASC
");

while ($chat = mysqli_fetch_assoc($query)):
    $isMine = $chat['sender_id'] == $user_id;
?>
    <div class="d-flex mb-3 <?= $isMine ? 'justify-content-end' : 'justify-content-start' ?>">
        <div class="p-3 rounded-3 shadow-sm <?= $isMine ? 'bg-success text-white' : 'bg-white' ?>" style="max-width:70%;">
            <small class="d-block fw-semibold mb-1"><?= htmlspecialchars($chat['sender_name']) ?></small>
            <div><?= nl2br(htmlspecialchars($chat['message'])) ?></div>
            <div class="text-end">
                <small class="text-muted" style="font-size:0.75rem;"><?= date('H:i', strtotime($chat['created_at'])) ?></small>
            </div>
        </div>
    </div>
<?php endwhile; ?>