<?php
// Include the db.php file to establish a database connection and define the query function
require_once 'db.php';

// Get the JSON data from the AJAX request
$data = json_decode(file_get_contents('php://input'), true);

// Extract the workout name and data from the JSON
$workoutName = $data['workoutName'];
$workoutData = $data['workoutData'];

// Create a new workout entry in the database
$query = "INSERT INTO workouts (name, user_id, is_public) VALUES ('$workoutName', 1, 0)"; // Replace 1 with the actual user ID
$queryResult = query($query);
if (!$queryResult) {
  die("Failed to create the workout: " . mysqli_error($conn));
}

// Get the ID of the newly created workout
$workoutId = mysqli_insert_id($conn);

// Iterate over each exercise in the workout data and store it in the database
foreach ($workoutData as $exercise) {
  $item = $exercise['item'];
  $exerciseName = $exercise['exercise'];
  $seconds = $exercise['seconds'];
  $sets = $exercise['sets'];

  // Get the exercise ID from the database
  $query = "SELECT id FROM exercises WHERE name = '$exerciseName'";
  $queryResult = query($query);
  $row = mysqli_fetch_assoc($queryResult);
  $exerciseId = $row['id'];

  // Store the exercise in the workout_sequence table
  $query = "INSERT INTO workout_sequence (workout_id, exercise_id, rest_period_id, sequence_order, work_period, reps) VALUES ($workoutId, $exerciseId, NULL, NULL, $seconds, $sets)";
  $queryResult = query($query);
  if (!$queryResult) {
    die("Failed to store exercise in the database: " . mysqli_error($conn));
  }
}

// Return a response indicating success
$response = ['success' => true];
echo json_encode($response);
