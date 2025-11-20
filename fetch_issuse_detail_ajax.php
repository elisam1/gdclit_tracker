<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'it') {
    echo json_encode(['error'=>'Unauthorized']);
    exit();
}

if (!isset($_GET['id'])) {
    echo json_encode(['error'=>'No issue ID provided']);
    exit();
}

$issue_id = intval($_GET['id']);
$sql = "SELECT issues.*, users.name AS reporter_name 
        FROM issues 
        JOIN users ON issues.user_id = users.id
        WHERE issues.id='$issue_id' LIMIT 1";
$result = mysqli_query($conn, $sql);

if ($issue = mysqli_fetch_assoc($result)) {
    echo json_encode($issue);
} else {
    echo json_encode(['error'=>'Issue not found']);
}
?>
