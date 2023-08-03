<?php
require_once '../../php/db_connect.php';

$exercise_id = $_POST['exercise_id'];
$progression_exercise_id = $_POST['progression_exercise_id'];
$threshold = $_POST['threshold'];
$sequence_order = $_POST['sequence_order'];
$next_exercise_id = $_POST['next_exercise_id'];

$query = "INSERT INTO progressions (exercise_id, progression_exercise_id, sequence_order, next_exercise_id, threshold) VALUES (?, ?, ?, ?, ?)";

$stmt = $conn->prepare($query);
$stmt->bind_param("iiiii", $exercise_id, $progression_exercise_id, $sequence_order, $next_exercise_id, $threshold);
$stmt->execute();

$result = ['success' => $stmt->affected_rows > 0];

echo json_encode($result);
?>
