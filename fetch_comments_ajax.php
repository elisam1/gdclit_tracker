<?php
session_start();
require_once 'db.php';
header('Content-Type: application/json');

if (!isset($_GET['issue_id'])) {
    echo json_encode([]);
    exit();
}

$issue_id = intval($_GET['issue_id']);

$sql = "SELECT comments.comment, comments.created_at, users.name AS commenter_name
        FROM comments
        JOIN users ON comments.user_id = users.id
        WHERE comments.issue_id = ?
        ORDER BY comments.created_at ASC";

if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param('i', $issue_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $comments = [];
    while ($row = $result->fetch_assoc()) {
        $comments[] = $row;
    }
    $stmt->close();

    echo json_encode($comments);
} else {
    echo json_encode([]);
}
?>
