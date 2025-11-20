<?php
session_start();
require_once 'db.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['pending'=>0,'in_progress'=>0,'resolved'=>0]);
    exit();
}

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

$pending = $in_progress = $resolved = 0;

if ($role == 'staff') {
    $sql = "SELECT status, COUNT(*) as count FROM issues WHERE user_id='$user_id' GROUP BY status";
} else {
    $sql = "SELECT status, COUNT(*) as count FROM issues GROUP BY status";
}

$result = mysqli_query($conn, $sql);

while ($row = mysqli_fetch_assoc($result)) {
    switch($row['status']) {
        case 'Pending': $pending = $row['count']; break;
        case 'In Progress': $in_progress = $row['count']; break;
        case 'Resolved': $resolved = $row['count']; break;
    }
}

echo json_encode([
    'pending' => $pending,
    'in_progress' => $in_progress,
    'resolved' => $resolved
]);
