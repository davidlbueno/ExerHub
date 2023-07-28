<?php
require_once '../../php/db_connect.php';
require_once '../../php/db_delete.php';

header('Content-Type: application/json');

$exerciseId = $_POST['exerciseId'];

// Delete the exercise from the exercises table
$delete($conn, 'DELETE FROM exercises WHERE id = ?', [$exerciseId]);

// Delete the exercise description from the exercise_descriptions table
$delete($conn, 'DELETE FROM exercise_descriptions WHERE exercise_id = ?', [$exerciseId]);

// Delete the associated muscles from the exercise_muscles table
$delete($conn, 'DELETE FROM exercise_muscles WHERE exercise_id = ?', [$exerciseId]);

echo json_encode(['status' => 'success']);
?>
