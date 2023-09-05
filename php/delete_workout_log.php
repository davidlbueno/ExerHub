<?php
require_once 'php/db_connect.php';
require_once 'php/db_query.php';

// Get the workout ID from the request payload
$requestPayload = file_get_contents('php://input');
$data = json_decode($requestPayload, true);
$logId = $data['log_id']; // Use log_Id instead of logId

// Print the logId to the web console
echo "<script>console.log('logId:', " . json_encode($logId) . ");</script>";

$deleteLogItemsQuery = "DELETE FROM workout_log_items WHERE workout_log_id = ?";
post($conn, $deleteLogItemsQuery, [$logId]);

$deleteLogsQuery = "DELETE FROM workout_logs WHERE id = ?";
post($conn, $deleteLogsQuery, [$logId]);

$queryResult1 = post($conn, $deleteLogItemsQuery, [$logId]);
$queryResult2 = post($conn, $deleteLogsQuery, [$logId]);

if ($queryResult1 && $queryResult2) {
  echo "Workout log deleted successfully";
} else {
  echo "Failed to delete workout log";
}

?>
