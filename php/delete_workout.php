<?php
require_once 'db.php'; // Assuming you have a separate file for the database connection

// Get the workout ID from the request payload
$requestPayload = file_get_contents('php://input');
$data = json_decode($requestPayload, true);
$workoutId = $data['workoutId'];

// Delete workout sequences associated with the workout ID
$deleteSequencesQuery = "DELETE FROM workout_sequences WHERE workout_id = $workoutId";
$queryResult = query($deleteSequencesQuery);

// Delete workout logs associated with the workout ID
$deleteLogsQuery = "DELETE FROM workout_logs WHERE workout_id = $workoutId";
$queryResult = query($deleteLogsQuery);

// Delete the workout
$deleteWorkoutQuery = "DELETE FROM workouts WHERE id = $workoutId";
$queryResult = query($deleteWorkoutQuery);

if ($queryResult) {
  // Return a success message or any other response
  echo "Workout deleted successfully";
} else {
  // Return an error message or any other response
  echo "Failed to delete workout";
}
?>
