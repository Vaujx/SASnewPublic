<?php
require_once 'dbh.inc.php';

$id = $_GET['i
d'];
$service = $_GET['service'];

$sql = "DELETE FROM events WHERE id = ? AND service = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("is", $id, $service);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => $conn->error]);
}

$stmt->close();
$conn->close();
?>