<?php
session_start();
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email'] ?? '');
    $password_input = $_POST['password'] ?? '';

    // Fetch user by email using prepared statement
    $stmt = $conn->prepare("SELECT id, name, role, password FROM users WHERE email = ? LIMIT 1");
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($user = $result->fetch_assoc()) {
        $stored = $user['password'];
        $is_valid = false;

        // Forward-compatible: support password_hash() if present; else fallback to md5
        if (preg_match('/^\$2y\$|^\$2a\$|^\$argon2/', $stored)) {
            $is_valid = password_verify($password_input, $stored);
        } else {
            $is_valid = (md5($password_input) === $stored);
        }

        if ($is_valid) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['name'] = $user['name'];
            $_SESSION['role'] = $user['role'];
            header("Location: dashboard.php");
            exit();
        } else {
            $error = "Invalid email or password.";
        }
    } else {
        $error = "Invalid email or password.";
    }

    $stmt->close();
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
