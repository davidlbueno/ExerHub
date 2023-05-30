<?php
session_start(); // Start the session if not already started
// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
  // Redirect the user to the login page or display an error message
  header("Location: login.php"); // Replace "login.php" with your actual login page URL
  exit();
}
// Include the database connection file
require_once 'db.php';
// Get the raw request body
$requestBody = file_get_contents('php://input');
// Parse the JSON data
$requestData = json_decode($requestBody, true);
// Get the workout name and user ID from the request data
$workoutName = $requestData['workoutName'];
$workoutData = $requestData['workoutData'];
$userId = $_SESSION['user_id'];
// Create the workout record in the 'workouts' table
$query = "INSERT INTO workouts (name, user_id, is_public) VALUES ('$workoutName', $userId, '0')";
$result = query($query);
// Check if the query was successful
if ($result) {
  //Workout record created successfully
  $workoutId = mysqli_insert_id($conn); // Get the auto-generated workout ID
  // Process the workout data
foreach ($workoutData as $type) {
  $typeValue = $type['type'];
  $exerciseValue = $type['exercise'];
  $secondsValue = $type['seconds'];
  // Get the exercise ID based on the exercise name
  $exerciseQuery = "SELECT id FROM exercises WHERE name = '$exerciseValue'";
  $exerciseResult = query($exerciseQuery);
  if ($typeValue != 'Rest') {
    if ($exerciseResult && mysqli_num_rows($exerciseResult) > 0) {
      $exerciseRow = mysqli_fetch_assoc($exerciseResult);
      $exerciseId = $exerciseRow['id'];
      // Insert the workout type into the database table
      $query = "INSERT INTO workout_sequences(workout_id, type, exercise_id, seconds)
                VALUES ($workoutId, '$typeValue', $exerciseId, $secondsValue)";
    } else {
      // Exercise not found, handle the error accordingly
      die("Error: Exercise not found for name '$exerciseValue'");
    }
  } else {
    // Insert the workout type into the database table without exercise_id
    $query = "INSERT INTO workout_sequences(workout_id, type, seconds)
              VALUES ($workoutId, '$typeValue', $secondsValue)";
  }    
  $result = query($query);
  if (!$result) {
    // Handle the error (e.g., display an error message, rollback changes, etc.)
    die("Error creating workout type: " . mysqli_error($conn));
  }
}
  // Redirect the user to the success page or display a success message
  header("Location: ../workouts.php"); // Replace "workout_success.php" with your actual success page URL
  exit();
} else {
  // Handle the error (e.g., display an error message, rollback changes, etc.)
  die("Error creating workout: " . mysqli_error($conn));
}
