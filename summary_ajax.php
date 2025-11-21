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
    $stmt = $conn->prepare('SELECT status, COUNT(*) as count FROM issues WHERE user_id = ? GROUP BY status');
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    // No params; safe to run directly
    $result = mysqli_query($conn, "SELECT status, COUNT(*) as count FROM issues GROUP BY status");
}

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
if (isset($stmt)) { $stmt->close(); }
