<?php
require_once 'db_connect.php';
require_once 'db_query.php';

$type = $_GET['type'];
$includeDifficulty = isset($_GET['includeDifficulty']) ? $_GET['includeDifficulty'] : false;


if ($includeDifficulty) {
  $stmt = $conn->prepare("SELECT name, id, difficulty FROM exercises WHERE type=?");
} else {
  $stmt = $conn->prepare("SELECT name, id FROM exercises WHERE type=?");
}

$stmt->bind_param("s", $type);

$stmt->execute();

$result = $stmt->get_result();
$exercises = [];
while ($row = $result->fetch_assoc()) {
  $exercises[] = $row;
}

$stmt->close();

header('Content-Type: application/json');
echo json_encode($exercises);
?>
