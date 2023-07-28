<?php
require_once '../../php/db_connect.php';

$conn = db_connect();

$result = $conn->query('SELECT id, name FROM muscles');

$muscleIds = [];
while ($row = $result->fetch_assoc()) {
    $muscleIds[$row['name']] = $row['id'];
}

echo json_encode($muscleIds);
?>
