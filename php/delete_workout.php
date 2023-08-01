<?php
require_once 'db_connect.php';
require_once 'db_query.php';
require_once 'db_post.php';

// Get the workout ID from the request payload
$requestPayload = file_get_contents('php://input');
$data = json_decode($requestPayload, true);
$workoutId = $data['workoutId'];

$deleteUserSelectedWorkoutsQuery = "DELETE FROM user_selected_workouts WHERE workout_id = ?";
post($conn, $deleteUserSelectedWorkoutsQuery, [$workoutId]);

$deleteLogItemsQuery = "DELETE FROM workout_log_items WHERE workout_log_id IN (SELECT id FROM workout_logs WHERE workout_id = ?)";
post($conn, $deleteLogItemsQuery, [$workoutId]);

$deleteSequencesQuery = "DELETE FROM workout_sequences WHERE workout_id = ?";
post($conn, $deleteSequencesQuery, [$workoutId]);

$deleteLogsQuery = "DELETE FROM workout_logs WHERE workout_id = ?";
post($conn, $deleteLogsQuery, [$workoutId]);

$deleteWorkoutQuery = "DELETE FROM workouts WHERE id = ?";
$queryResult = post($conn, $deleteWorkoutQuery, [$workoutId]);

if ($queryResult) {
  // Return a success message or any other response
  echo "Workout deleted successfully";
} else {
  // Return an error message or any other response
  echo "Failed to delete workout";
}
?>
