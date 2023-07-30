<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../../php/db_connect.php';
require_once '../../php/db_post.php';

header('Content-Type: application/json');

$exerciseId = $_POST['exerciseId'];
$exerciseName = $_POST['exerciseName'];
$exerciseDescription = $_POST['exerciseDescription'];
$muscleIds = isset($_POST['muscleIds']) ? $_POST['muscleIds'] : []; // Do we need this?

// Update the exercise in the exercises table
post($conn, 'UPDATE exercises SET name = ? WHERE id = ?', [$exerciseName, $exerciseId]);

// Update the exercise description in the exercise_descriptions table
post($conn, 'UPDATE exercise_descriptions SET description = ? WHERE exercise_id = ?', [$exerciseDescription, $exerciseId]);

// Update the associated muscles in the exercise_muscles table
// First, delete all existing associated muscles
post($conn, 'DELETE FROM exercise_muscles WHERE exercise_id = ?', [$exerciseId]);

// Then, insert the new associated muscles
foreach ($_POST['muscles'] as $muscle) {
    post($conn, 'INSERT INTO exercise_muscles (exercise_id, muscle_id, intensity) VALUES (?, ?, ?)', [$exerciseId, $muscle['id'], $muscle['intensity']]);
}

echo json_encode(['status' => 'success']);
?>
