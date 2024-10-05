<?php
require_once 'dbh.inc.php'; 


$date = $_POST['date'];
$title = $_POST['title'];
$time = $_POST['time'];
$service = $_POST['service'];


$sql = "INSERT INTO calendar_events (title, date, time, service) VALUES (?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ssss", $title, $date, $time, $service);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => $conn->error]);
}

$stmt->close();
$conn->close();
?>