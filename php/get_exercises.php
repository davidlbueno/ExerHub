<?php
require_once 'db_connect.php';
require_once 'db_query.php';

$type = $_GET['type'];

$stmt = $conn->prepare("SELECT name FROM exercises WHERE type=?");
$stmt->bind_param("s", $type);

$stmt->execute();

$result = $stmt->get_result();
$exercises = [];
while ($row = $result->fetch_assoc()) {
  $exercises[] = [
    'name' => $row['name']
  ];
}

$stmt->close();

header('Content-Type: application/json');
echo json_encode($exercises);
?>
