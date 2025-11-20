<?php
session_start();
require_once 'db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'staff') {
    echo json_encode(['status'=>'error','message'=>'Unauthorized']);
    exit();
}

$user_id = $_SESSION['user_id'];
$title = mysqli_real_escape_string($conn, $_POST['title']);
$description = mysqli_real_escape_string($conn, $_POST['description']);
$category = mysqli_real_escape_string($conn, $_POST['category']);
$priority = mysqli_real_escape_string($conn, $_POST['priority']);

$attachment = '';
if(isset($_FILES['attachment']) && $_FILES['attachment']['name'] != ''){
    $fileName = time().'_'.basename($_FILES['attachment']['name']);
    $targetDir = 'uploads/';
    if(!is_dir($targetDir)) mkdir($targetDir, 0777, true);
    if(move_uploaded_file($_FILES['attachment']['tmp_name'], $targetDir.$fileName)){
        $attachment = $fileName;
    }
}

$sql = "INSERT INTO issues (user_id, title, description, category, priority, attachment, status, created_at, updated_at) 
        VALUES ('$user_id','$title','$description','$category','$priority','$attachment','Pending', NOW(), NOW())";

if(mysqli_query($conn, $sql)){
    echo json_encode(['status'=>'success']);
} else {
    echo json_encode(['status'=>'error','message'=>'Database error']);
}
?>
