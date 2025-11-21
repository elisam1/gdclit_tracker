<?php
session_start();
require_once 'db.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}
$user_id = $_SESSION['user_id'];

if (!isset($_POST['comment_id'], $_POST['comment'])) {
    echo json_encode(['error' => 'Missing data']);
    exit();
}
$comment_id = intval($_POST['comment_id']);
$comment_text = trim($_POST['comment']);
if ($comment_text === '') {
    echo json_encode(['error' => 'Comment cannot be empty']);
    exit();
}

// Verify ownership and edit window (15 minutes)
$stmt = $conn->prepare('SELECT user_id, TIMESTAMPDIFF(MINUTE, created_at, NOW()) AS age_min FROM comments WHERE id = ?');
$stmt->bind_param('i', $comment_id);
$stmt->execute();
$res = $stmt->get_result();
if ($row = $res->fetch_assoc()) {
    if ((int)$row['user_id'] !== (int)$user_id) {
        echo json_encode(['error' => 'Not allowed']);
        exit();
    }
    if ((int)$row['age_min'] > 15) {
        echo json_encode(['error' => 'Edit window expired']);
        exit();
    }
} else {
    echo json_encode(['error' => 'Comment not found']);
    exit();
}
$stmt->close();

// Update comment
$stmt_upd = $conn->prepare('UPDATE comments SET comment = ? WHERE id = ?');
$stmt_upd->bind_param('si', $comment_text, $comment_id);
if ($stmt_upd->execute()) {
    echo json_encode(['status' => 'success']);
} else {
    echo json_encode(['error' => 'Failed to update comment']);
}
$stmt_upd->close();
?>