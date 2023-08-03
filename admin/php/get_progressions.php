<?php
require_once 'db_connect.php';

$exercise_id = $_GET['exercise_id'];

$query = "SELECT e.name, p.progression_exercise_id, p.threshold, p.sequence_order FROM progressions p JOIN exercises e ON p.progression_exercise_id = e.id WHERE p.exercise_id = ?";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $exercise_id);
$stmt->execute();

$result = $stmt->get_result();
$data = $result->fetch_all(MYSQLI_ASSOC);

echo json_encode($data);
?>
