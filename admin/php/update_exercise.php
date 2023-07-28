<?php
require_once '../../php/db_connect.php';
require_once '../../php/db_post.php';

header('Content-Type: application/json');

$exerciseId = $_POST['exerciseId'];
$exerciseName = $_POST['exerciseName'];
$exerciseDescription = $_POST['exerciseDescription'];
$muscleIds = isset($_POST['muscleIds']) ? $_POST['muscleIds'] : [];

// Update the exercise in the exercises table
$post($conn, 'UPDATE exercises SET name = ? WHERE id = ?', [$exerciseName, $exerciseId]);

// Update the exercise description in the exercise_descriptions table
$post($conn, 'UPDATE exercise_descriptions SET description = ? WHERE exercise_id = ?', [$exerciseDescription, $exerciseId]);

// Update the associated muscles in the exercise_muscles table
// First, delete all existing associated muscles
$post($conn, 'DELETE FROM exercise_muscles WHERE exercise_id = ?', [$exerciseId]);

// Then, insert the new associated muscles
foreach ($muscleIds as $muscleId) {
    $post($conn, 'INSERT INTO exercise_muscles (exercise_id, muscle_id) VALUES (?, ?)', [$exerciseId, $muscleId]);
}

echo json_encode(['status' => 'success']);
?>
