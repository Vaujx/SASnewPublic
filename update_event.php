<?php
require_once 'dbh.inc.php';
session_start(); // Start session for user authentication

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

// Validate and sanitize inputs
if (isset($_POST['id'], $_POST['title'], $_POST['time'], $_POST['service'])) {
    $id = (int) $_POST['id']; // Cast to integer for safety
    $title = trim($_POST['title']); // Trim whitespace
    $time = trim($_POST['time']); // Validate time format if needed
    $service = trim($_POST['service']); // Additional validation can be added

    // Prepare SQL query to update the event
    $sql = "UPDATE calendar_events SET title = ?, time = ?, service = ? WHERE id = ?";
    $stmt = $pdo->prepare($sql);

    // Bind parameters
    $stmt->bindParam(1, $title);
    $stmt->bindParam(2, $time);
    $stmt->bindParam(3, $service);
    $stmt->bindParam(4, $id);

    // Execute query and check for success
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        // Log error internally, don't expose it to the user
        error_log("Database Error: " . print_r($stmt->errorInfo(), true));
        echo json_encode(['success' => false, 'message' => 'Failed to update event']);
    }

    // Close the statement
    $stmt->closeCursor();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid input data']);
}

// Close the database connection
$pdo = null;
?>