<?php
session_start();
require_once 'db.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode([]);
    exit();
}

$role = $_SESSION['role'];
$user_id = $_SESSION['user_id'];

$issues = [];

if ($role === 'staff') {
    // Staff: show only their issues, include reporter name
    $sql = "SELECT issues.id, issues.title, issues.category, issues.priority, issues.status, issues.created_at, users.name AS reporter_name
            FROM issues JOIN users ON issues.user_id = users.id
            WHERE issues.user_id = ?
            ORDER BY issues.created_at DESC
            LIMIT 50";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        $res = $stmt->get_result();
        while ($row = $res->fetch_assoc()) {
            $issues[] = $row;
        }
        $stmt->close();
    }
} else {
    // IT: show all issues, include reporter name
    $sql = "SELECT issues.id, issues.title, issues.category, issues.priority, issues.status, issues.created_at, users.name AS reporter_name
            FROM issues JOIN users ON issues.user_id = users.id
            ORDER BY issues.created_at DESC
            LIMIT 50";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->execute();
        $res = $stmt->get_result();
        while ($row = $res->fetch_assoc()) {
            $issues[] = $row;
        }
        $stmt->close();
    }
}

echo json_encode($issues);
?>