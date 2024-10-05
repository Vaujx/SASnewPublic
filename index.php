<?php
session_start();


include 'dbh.inc.php';


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // SQL query to find the user by email
    $sql = "SELECT * FROM users WHERE email = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    // Verify user and password
    if ($user && password_verify($password, $user['password'])) {
        // Store user data in session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_role'] = $user['role'];

        // Redirect based on role
        if ($user['role'] == 'admin') {
            header("Location: admin_dashboard.php");
        } elseif ($user['role'] == 'staff') {
            header("Location: staff_dashboard.php");
        } else {
            header("Location: student_dashboard.php");
        }
        exit();
    } else {
        $error = "Invalid email or password.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Page</title>
</head>
<body>
    <h1>Login</h1>

    <!-- Display login error if any -->
    <?php if (!empty($error)) : ?>
        <p style="color: red;"><?php echo $error; ?></p>
    <?php endif; ?>

    <form method="POST" action="index.php">
        <label for="email">Email:</label><br>
        <input type="email" id="email" name="email" required><br>

        <label for="password">Password:</label><br>
        <input type="password" id="password" name="password" required><br><br>

        <button type="submit">Login</button>
    </form>

    <p>Don't have an account? <a href="register.php">Register here</a>.</p>
</body>
</html>