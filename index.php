<?php
session_start();
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = md5($_POST['password']); // encrypt the password

    $query = "SELECT * FROM users WHERE email='$email' AND password='$password'";
    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) == 1) {
        $user = mysqli_fetch_assoc($result);
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['name'] = $user['name'];
        $_SESSION['role'] = $user['role'];

        header("Location: dashboard.php");
        exit();
    } else {
        $error = "Invalid email or password.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login - Issue Tracker</title>
    <style>
        body { font-family: Arial; background: #f5f5f5; }
        .login-box {
            width: 300px; margin: 100px auto; padding: 20px;
            background: #fff; border-radius: 10px; box-shadow: 0 0 5px #aaa;
        }
        input { width: 100%; padding: 8px; margin: 10px 0; }
        button { width: 100%; padding: 8px; background: #007BFF; color: white; border: none; border-radius: 5px; }
        .error { color: red; text-align: center; }
    </style>
</head>
<body>

<div class="login-box">
    <h3>Login</h3>
    <form method="POST">
        <input type="email" name="email" placeholder="Email" required>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit">Login</button>
    </form>
    <?php if (!empty($error)) echo "<p class='error'>$error</p>"; ?>
</div>

</body>
</html>
