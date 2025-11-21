<?php
session_start();
require_once 'db.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'it') {
    http_response_code(403);
    echo json_encode([]);
    exit();
}

// Fetch all issues with reporter name
$sql = "SELECT issues.*, users.name AS reporter_name
        FROM issues
        JOIN users ON issues.user_id = users.id
        ORDER BY issues.created_at DESC";

$result = mysqli_query($conn, $sql);
$issues = [];

while($row = mysqli_fetch_assoc($result)) {
    $issues[] = $row;
}

echo json_encode($issues);
?>
