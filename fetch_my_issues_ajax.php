<?php
session_start();
require_once 'db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'staff') {
    echo json_encode([]);
    exit();
}

$user_id = $_SESSION['user_id'];
$issues = [];

$stmt = $conn->prepare('SELECT * FROM issues WHERE user_id = ? ORDER BY created_at DESC');
$stmt->bind_param('i', $user_id);
$stmt->execute();
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) {
    $issues[] = $row;
}
$stmt->close();

echo json_encode($issues);
?>
