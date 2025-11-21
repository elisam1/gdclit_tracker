<?php
session_start();
require_once 'db.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["error" => "Unauthorized"]);
    exit();
}

$user_id = $_SESSION['user_id'];

if (!isset($_POST['issue_id'], $_POST['comment'])) {
    echo json_encode(['error' => 'Missing data']);
    exit();
}

$issue_id = intval($_POST['issue_id']);
$comment_text = trim($_POST['comment']);

if ($comment_text === '') {
    echo json_encode(['error' => 'Comment cannot be empty']);
    exit();
}

// Optional: verify issue exists
$stmt_issue = $conn->prepare('SELECT id FROM issues WHERE id = ?');
$stmt_issue->bind_param('i', $issue_id);
$stmt_issue->execute();
$res_issue = $stmt_issue->get_result();
if ($res_issue->num_rows === 0) {
    echo json_encode(['error' => 'Issue not found']);
    exit();
}
$stmt_issue->close();

// Insert comment with timestamp
$stmt = $conn->prepare('INSERT INTO comments (issue_id, user_id, comment, created_at) VALUES (?, ?, ?, NOW())');
$stmt->bind_param('iis', $issue_id, $user_id, $comment_text);

if ($stmt->execute()) {
    $comment_id = $stmt->insert_id;
    $stmt->close();

    // Fetch commenter name
    $stmt_user = $conn->prepare('SELECT name FROM users WHERE id = ?');
    $stmt_user->bind_param('i', $user_id);
    $stmt_user->execute();
    $res_user = $stmt_user->get_result();
    $user = $res_user->fetch_assoc();
    $stmt_user->close();

    echo json_encode([
        'id' => $comment_id,
        'comment' => $comment_text,
        'created_at' => date('Y-m-d H:i:s'),
        'commenter_name' => $user['name'] ?? ''
    ]);
} else {
    echo json_encode(['error' => 'Failed to post comment']);
}
?>
