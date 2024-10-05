<?php
require_once 'dbh.inc.php';

$date = $_GET['date'];
$service = $_GET['service'];

$sql = "SELECT id, title, TIME_FORMAT(time, '%H:%i') as time FROM events WHERE date = ? AND service = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $date, $service);
$stmt->execute();
$result = $stmt->get_result();

$events = [];
while ($row = $result->fetch_assoc()) {
    $events[] = $row;
}

echo json_encode($events);

$stmt->close();
$conn->close();
?>