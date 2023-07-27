<?php
require_once 'db_connect.php';
require_once 'db_query.php';

// Get the workout ID from the request payload
$requestPayload = file_get_contents('php://input');
$data = json_decode($requestPayload, true);
$workoutId = $data['workoutId'];

// Delete user selected workouts associated with the workout ID
$deleteUserSelectedWorkoutsQuery = "DELETE FROM user_selected_workouts WHERE workout_id = $workoutId";
$queryResult = query($conn, $deleteUserSelectedWorkoutsQuery);

// Delete workout log items associated with the workout ID
$deleteLogItemsQuery = "DELETE FROM workout_log_items WHERE workout_log_id IN (SELECT id FROM workout_logs WHERE workout_id = $workoutId)";
$queryResult = query($conn, $deleteLogItemsQuery);

// Delete workout sequences associated with the workout ID
$deleteSequencesQuery = "DELETE FROM workout_sequences WHERE workout_id = $workoutId";
$queryResult = query($conn, $deleteSequencesQuery);

// Delete workout logs associated with the workout ID
$deleteLogsQuery = "DELETE FROM workout_logs WHERE workout_id = $workoutId";
$queryResult = query($conn, $deleteLogsQuery);

// Delete the workout
$deleteWorkoutQuery = "DELETE FROM workouts WHERE id = $workoutId";
$queryResult = query($conn, $deleteWorkoutQuery);

if ($queryResult) {
  // Return a success message or any other response
  echo "Workout deleted successfully";
} else {
  // Return an error message or any other response
  echo "Failed to delete workout";
}
?>
