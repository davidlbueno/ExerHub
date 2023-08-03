<?php
require_once '../../php/db_connect.php';

$exercise_id = $_POST['exercise_id'];
$progression_exercise_ids = $_POST['progression_exercise_ids'];

$placeholders = implode(',', array_fill(0, count($progression_exercise_ids), '?'));
$query = "DELETE FROM progressions WHERE exercise_id = ? AND progression_exercise_id IN ($placeholders)";

$stmt = $conn->prepare($query);
$stmt->bind_param(str_repeat('i', count($progression_exercise_ids) + 1), $exercise_id, ...$progression_exercise_ids);
$stmt->execute();

$result = ['success' => $stmt->affected_rows > 0];

echo json_encode($result);
?>
