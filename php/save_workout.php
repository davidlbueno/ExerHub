<?php
session_start(); // Start the session if not already started
// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
  // Redirect the user to the login page or display an error message
  header("Location: login.php"); // Replace "login.php" with your actual login page URL
  exit();
}
// Include the database connection file
require_once 'db_connect.php';
require_once 'db_query.php';
// Get the raw request body
$requestBody = file_get_contents('php://input');
// Parse the JSON data
$requestData = json_decode($requestBody, true);
// Get the workout name and user ID from the request data
$workoutName = $requestData['workoutName'];
$workoutData = $requestData['workoutData'];
$userId = $_SESSION['user_id'];

// Create the workout record in the 'workouts' table
$query = "INSERT INTO workouts (name, user_id, is_public) VALUES (?, ?, '0')";

$params = array($workoutName, $userId);
$result = query($conn, $query, $params);

// Check if the query was successful
if ($result['success']) {
  //Workout record created successfully
  $workoutId = $result['insert_id']; // Get the auto-generated workout ID
  // Process the workout data
  foreach ($workoutData as $type) {
    $typeValue = $type['type'];
    $exerciseValue = $type['exercise'];
    $secondsValue = $type['seconds'];
    $warmupValue = $type['warmup'];
    // Get the exercise ID based on the exercise name
    $exerciseQuery = "SELECT id FROM exercises WHERE name = ?";
    $exerciseResult = query($conn, $exerciseQuery, array($exerciseValue));
    if ($typeValue != 'Rest') {
      if (!empty($exerciseResult)) {
        $exerciseId = $exerciseResult[0]['id'];
        // Insert the exercise into the workout sequence for the workout in the database
        $query = "INSERT INTO workout_sequences(workout_id, type, exercise_id, seconds, warmup)
                  VALUES (?, ?, ?, ?, ?)";
        $params = array($workoutId, $typeValue, $exerciseId, $secondsValue, $warmupValue);
      } else {
        // Exercise not found, handle the error accordingly
        die("Error: Exercise not found for name '$exerciseValue'");
      }
    } else {
      // Insert the workout type into the database table without exercise_id
      $query = "INSERT INTO workout_sequences(workout_id, type, seconds)
                VALUES (?, ?, ?)";
      $params = array($workoutId, $typeValue, $secondsValue);
    }    
    $result = query($conn, $query, $params);
    if (!$result['success']) {
      // Handle the error (e.g., display an error message, rollback changes, etc.)
      die("Error creating workout type: " . $result['error']);
    }
  }
  echo json_encode(['success' => true]);
  exit();
} else {
  // Handle the error (e.g., display an error message, rollback changes, etc.)
  die("Error creating workout: " . $result['error']);
}
?>
