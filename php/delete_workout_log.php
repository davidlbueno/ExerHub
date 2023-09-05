<?php
require_once 'db_connect.php';
require_once 'db_query.php';

// Get the workout ID from the request payload
$requestPayload = file_get_contents('php://input');
$data = json_decode($requestPayload, true);
$logId = $data['log_id']; // Use log_id instead of logId

// Delete log items
$deleteLogItemsQuery = "DELETE FROM workout_log_items WHERE workout_log_id = ?";
$result1 = query($conn, $deleteLogItemsQuery, [$logId]);

// Delete logs
$deleteLogsQuery = "DELETE FROM workout_logs WHERE id = ?";
$result2 = query($conn, $deleteLogsQuery, [$logId]);

// Check if both queries were successful
if ($result1['success'] && $result2['success']) {
  echo "Workout log deleted successfully";
} else {
  echo "Failed to delete workout log";
}
?>
