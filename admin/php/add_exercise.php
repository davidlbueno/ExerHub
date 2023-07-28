<?php
require_once '../../php/db_connect.php';
require_once '../../php/db_post.php';

header('Content-Type: application/json');

$exerciseName = $_POST['exerciseName'];
$exerciseDescription = $_POST['exerciseDescription'];
$muscleIds = $_POST['muscleIds'];

// Insert the new exercise into the exercises table
$post($conn, 'INSERT INTO exercises (name) VALUES (?)', [$exerciseName]);

// Get the ID of the newly inserted exercise
$exerciseId = mysqli_insert_id($conn);

// Insert the exercise description into the exercise_descriptions table
$post($conn, 'INSERT INTO exercise_descriptions (exercise_id, description) VALUES (?, ?)', [$exerciseId, $exerciseDescription]);

// Insert the associated muscles into the exercise_muscles table
foreach ($muscleIds as $muscleId) {
    $post($conn, 'INSERT INTO exercise_muscles (exercise_id, muscle_id) VALUES (?, ?)', [$exerciseId, $muscleId]);
}

echo json_encode(['status' => 'success']);
?>
