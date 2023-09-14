<?php
require_once 'db_connect.php';
require_once 'db_query.php';
require_once 'db_post.php';

$logId = $_POST['log_id'];
$exerciseTypes = $_POST['exercise_type'];
$exerciseIds = $_POST['exercise_id'];  // Get exercise IDs from the form
$exerciseTimes = $_POST['exercise_time'];
$reps = $_POST['reps'];

// Delete existing log items for this log
$deleteQuery = "DELETE FROM workout_log_items WHERE workout_log_id = ?";
post($conn, $deleteQuery, [$logId]);

// Insert new log items
for ($i = 0; $i < count($exerciseTypes); $i++) {
    $exerciseType = $exerciseTypes[$i];
    $exerciseId = $exerciseIds[$i];  // Use exercise ID
    $exerciseTime = $exerciseTimes[$i];
    $rep = $reps[$i];

    $insertQuery = "INSERT INTO workout_log_items (workout_log_id, exercise_type, exercise_id, exercise_time, reps) VALUES (?, ?, ?, ?, ?)";
    post($conn, $insertQuery, [$logId, $exerciseType, $exerciseId, $exerciseTime, $rep]);
}

header('Location: /logs.php');
?>
