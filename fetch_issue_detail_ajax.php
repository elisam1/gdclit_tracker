<?php
session_start();
require_once 'db.php';
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

$role = $_SESSION['role'];
$user_id = $_SESSION['user_id'];

// Check if issue ID is provided
if (!isset($_GET['id'])) {
    echo json_encode(['error' => 'No issue ID provided']);
    exit();
}

$issue_id = intval($_GET['id']);

// Prepare statement to fetch issue and reporter
$stmt = $conn->prepare("
    SELECT issues.*, users.name AS reporter_name
    FROM issues
    JOIN users ON issues.user_id = users.id
    WHERE issues.id = ? LIMIT 1
");
$stmt->bind_param("i", $issue_id);
$stmt->execute();
$result = $stmt->get_result();

if ($issue = $result->fetch_assoc()) {
    // Check if user can view this issue
    if ($role != 'it' && $issue['user_id'] != $user_id) {
        echo json_encode(['error' => 'Unauthorized']);
        exit();
    }

    // Fetch comments
    $stmt_comments = $conn->prepare("
        SELECT comments.id, comments.comment, comments.created_at, comments.user_id AS commenter_id, users.name AS commenter_name
        FROM comments
        JOIN users ON comments.user_id = users.id
        WHERE comments.issue_id = ?
        ORDER BY comments.created_at ASC
    ");
    $stmt_comments->bind_param("i", $issue_id);
    $stmt_comments->execute();
    $comments_result = $stmt_comments->get_result();

    $comments = [];
    while ($comment = $comments_result->fetch_assoc()) {
        $comments[] = $comment;
    }

    $issue['comments'] = $comments;

    echo json_encode($issue);
} else {
    echo json_encode(['error' => 'Issue not found']);
}
?>
