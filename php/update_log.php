<?php
header('Content-Type: application/json');

require_once 'db_connect.php';
require_once 'db_query.php';
require_once 'db_post.php';

$logId = $_POST['log_id'];
$exerciseTypes = $_POST['exercise_type'];
$exerciseIds = $_POST['exercise_id'];
$exerciseTimes = $_POST['exercise_time'];
$reps = $_POST['reps'];
$warmup = $_POST['warmup'];

// Delete existing log items for this log
$deleteQuery = "DELETE FROM workout_log_items WHERE workout_log_id = ?";
post($conn, $deleteQuery, [$logId]);

// Insert new log items
foreach ($exerciseTypes as $i => $exerciseType) {
  $exerciseId = $exerciseIds[$i];
  $exerciseTime = $exerciseTimes[$i];
  $rep = $reps[$i];

  $insertQuery = "INSERT INTO workout_log_items (workout_log_id, exercise_type, exercise_id, exercise_time, reps, warmup) VALUES (?, ?, ?, ?, ?, ?)";
  post($conn, $insertQuery, [$logId, $exerciseType, $exerciseId, $exerciseTime, $rep, $warmup[$i]]);

}

echo json_encode(['success' => true]);
?>
