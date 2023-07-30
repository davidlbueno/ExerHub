<?php
require_once '../../php/db_connect.php';
require_once '../../php/db_post.php';


$exerciseName = $_POST['exerciseName'];
$exerciseType = $_POST['exerciseType'];
$exerciseDifficulty = $_POST['exerciseDifficulty'];
$description = $_POST['description'];
$muscles = $_POST['muscles'];  // Get the muscle data array

$conn = db_connect();

// Insert the new exercise into the exercises table
post($conn, 'INSERT INTO exercises (name, type, difficulty) VALUES (?, ?, ?)', [$exerciseName, $exerciseType, $exerciseDifficulty]);

// Get the ID of the newly inserted exercise
$exerciseId = $conn->lastInsertId();

// Insert the exercise description into the exercise_descriptions table
post($conn, 'INSERT INTO exercise_descriptions (exercise_id, description) VALUES (?, ?)', [$exerciseId, $description]);

// Insert the muscle data into the exercise_muscles table
foreach ($muscles as $muscle) {
  $muscleId = $muscle['muscleId'];
  $intensity = $muscle['intensity'];
  post($conn, 'INSERT INTO exercise_muscles (exercise_id, muscle_id, intensity) VALUES (?, ?, ?)', [$exerciseId, $muscleId, $intensity]);
}
?>
