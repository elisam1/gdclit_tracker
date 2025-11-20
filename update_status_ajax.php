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
$status = mysqli_real_escape_string($conn, $_POST['status']);

// Update status
$sql = "UPDATE issues SET status='$status', updated_at=NOW() WHERE id='$issue_id'";
if(mysqli_query($conn, $sql)) {

    // Send email to reporter
    $sql_user = "SELECT users.email, users.name, issues.title 
                 FROM issues JOIN users ON issues.user_id = users.id 
                 WHERE issues.id='$issue_id'";
    $res_user = mysqli_query($conn, $sql_user);
    if($user = mysqli_fetch_assoc($res_user)){
        $to = $user['email'];
        $subject = "Update on your reported issue: ".$user['title'];
        $message = "Hello ".$user['name'].",\n\n";
        $message .= "The status of your reported issue '".$user['title']."' has been updated to: ".$status.".\n\n";
        $message .= "Best regards,\nIT Support Team";
        $headers = "From: no-reply@company.com\r\n";
        @mail($to, $subject, $message, $headers);
    }

    echo 'success';
} else {
    echo 'error';
}
?>
