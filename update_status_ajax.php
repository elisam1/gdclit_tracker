<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'it') {
    echo 'unauthorized';
    exit();
}

if (!isset($_POST['issue_id']) || !isset($_POST['status'])) {
    echo 'error';
    exit();
}

$issue_id = intval($_POST['issue_id']);
$status = trim($_POST['status']);
$comment = isset($_POST['comment']) ? trim($_POST['comment']) : '';

// Update status with prepared statement
$stmt_update = $conn->prepare('UPDATE issues SET status = ?, updated_at = NOW() WHERE id = ?');
$stmt_update->bind_param('si', $status, $issue_id);
if ($stmt_update->execute()) {
    $stmt_update->close();

    // Save comment if provided
    if ($comment !== '') {
        $user_id = $_SESSION['user_id'];
        $stmt_comment = $conn->prepare('INSERT INTO comments (issue_id, user_id, comment, created_at) VALUES (?, ?, ?, NOW())');
        $stmt_comment->bind_param('iis', $issue_id, $user_id, $comment);
        $stmt_comment->execute();
        $stmt_comment->close();
    }

    // Send email to reporter
    $stmt_user = $conn->prepare('SELECT users.email, users.name, issues.title FROM issues JOIN users ON issues.user_id = users.id WHERE issues.id = ?');
    $stmt_user->bind_param('i', $issue_id);
    $stmt_user->execute();
    $res_user = $stmt_user->get_result();
    if ($user = $res_user->fetch_assoc()) {
        $to = $user['email'];
        $subject = 'Update on your reported issue: ' . $user['title'];
        $message = "Hello " . $user['name'] . ",\n\n";
        $message .= "The status of your reported issue '" . $user['title'] . "' has been updated to: " . $status . ".\n\n";
        if ($comment !== '') {
            $message .= "Comment: " . $comment . "\n\n";
        }
        $message .= "Best regards,\nIT Support Team";
        $headers = "From: no-reply@company.com\r\n";
        @mail($to, $subject, $message, $headers);
    }
    $stmt_user->close();

    echo 'success';
} else {
    echo 'error';
}
?>
