<?php
session_start();
require_once 'db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'staff') {
    echo json_encode([]);
    exit();
}

$user_id = $_SESSION['user_id'];
$sql = "SELECT * FROM issues WHERE user_id='$user_id' ORDER BY created_at DESC";
$result = mysqli_query($conn, $sql);

$issues = [];
while($row = mysqli_fetch_assoc($result)){
    $issues[] = $row;
}
echo json_encode($issues);
?>
