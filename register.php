<?php
// Include database connection
include 'dbh.inc.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Hash the password
    $role = $_POST['role']; // The role chosen by the user (admin, student, staff)

    // SQL to insert new user
    $sql = "INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);

    if ($stmt->execute([$name, $email, $password, $role])) {
        echo "Registration successful!";
        header("Location: index.php"); // Redirect to login page after registration
        exit();
    } else {
        echo "Error: " . $pdo->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
</head>
<body>
    <h1>Register</h1>

    <form method="POST" action="register.php">
        <label for="name">Full Name:</label><br>
        <input type="text" id="name" name="name" required><br>

        <label for="email">Email:</label><br>
        <input type="email" id="email" name="email" required><br>

        <label for="password">Password:</label><br>
        <input type="password" id="password" name="password" required><br>

        <label for="role">Role:</label><br>
        <select id="role" name="role" required>
            <option value="student">Student</option>
            <option value="admin">Admin</option>
            <option value="staff">Staff</option>
        </select><br><br>

        <button type="submit">Register</button>
    </form>

    <p>Already have an account? <a href="index.php">Login here</a>.</p>
</body>
</html>