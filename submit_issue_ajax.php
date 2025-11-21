<?php
session_start();
require_once 'db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'staff') {
    echo json_encode(['status'=>'error','message'=>'Unauthorized']);
    exit();
}

$user_id = $_SESSION['user_id'];
$title = trim($_POST['title'] ?? '');
$description = trim($_POST['description'] ?? '');
$category = trim($_POST['category'] ?? '');
$priority = trim($_POST['priority'] ?? '');

// Basic validation
if ($title === '' || $description === '' || $category === '' || $priority === '') {
    echo json_encode(['status'=>'error','message'=>'Missing required fields']);
    exit();
}

// Handle file upload with validation
$attachment = '';
if(isset($_FILES['attachment']) && is_uploaded_file($_FILES['attachment']['tmp_name'])){
    $allowed_ext = ['jpg','jpeg','png','pdf','doc','docx'];
    $max_size = 5 * 1024 * 1024; // 5MB
    $orig_name = $_FILES['attachment']['name'];
    $tmp_path = $_FILES['attachment']['tmp_name'];
    $size = $_FILES['attachment']['size'] ?? 0;
    $ext = strtolower(pathinfo($orig_name, PATHINFO_EXTENSION));

    if ($size > $max_size) {
        echo json_encode(['status'=>'error','message'=>'Attachment too large']);
        exit();
    }
    if (!in_array($ext, $allowed_ext)) {
        echo json_encode(['status'=>'error','message'=>'Invalid attachment type']);
        exit();
    }

    $safe_base = preg_replace('/[^a-zA-Z0-9_\.-]/','_', basename($orig_name));
    $fileName = time().'_'.bin2hex(random_bytes(4)).'_'.$safe_base;
    $targetDir = 'uploads/';
    if(!is_dir($targetDir)) mkdir($targetDir, 0755, true);
    if(move_uploaded_file($tmp_path, $targetDir.$fileName)){
        $attachment = $fileName;
    }
}

// Insert using prepared statement
$stmt = $conn->prepare("INSERT INTO issues (user_id, title, description, category, priority, attachment, status, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, 'Pending', NOW(), NOW())");
$stmt->bind_param('isssss', $user_id, $title, $description, $category, $priority, $attachment);

if($stmt->execute()){
    echo json_encode(['status'=>'success']);
} else {
    echo json_encode(['status'=>'error','message'=>'Database error']);
}
$stmt->close();
?>
