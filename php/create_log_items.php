<?php
require_once 'db_connect.php';
require_once 'db_query.php';
require_once 'db_post.php';

function createWorkoutLogEntry($conn, $userId, $workoutId, $workoutStartTime, $workoutEndTime) {
    $query = "INSERT INTO workout_logs (user_id, workout_id, start_time, end_time) VALUES (?, ?, ?, ?)";
    $params = [$userId, $workoutId, $workoutStartTime, $workoutEndTime];
    return post($conn, $query, $params);
}

function createWorkoutLogItemEntry($conn, $workoutLogId, $exerciseType, $exerciseId, $exerciseTime, $exerciseReps, $warmup) {
    $query = "INSERT INTO workout_log_items (workout_log_id, exercise_type, exercise_id, exercise_time, reps, warmup) VALUES (?, ?, ?, ?, ?, ?)";
    $params = [$workoutLogId, $exerciseType, $exerciseId, $exerciseTime, $exerciseReps, $warmup];
    return post($conn, $query, $params);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['workoutLogEntry'])) {
        $userId = $_POST['userId'];
        $workoutId = $_POST['workoutId'];
        $workoutStartTime = $_POST['workoutStartTime'];
        $workoutEndTime = $_POST['workoutEndTime'];

        $result = createWorkoutLogEntry($conn, $userId, $workoutId, $workoutStartTime, $workoutEndTime);
        echo json_encode($result);
    }

    if (isset($_POST['workoutLogItemEntry'])) {
        $workoutLogId = $_POST['workoutLogId'];
        $exerciseType = $_POST['exerciseType'];
        $exerciseId = $_POST['exerciseId'];
        $exerciseTime = $_POST['exerciseTime'];
        $exerciseReps = $_POST['exerciseReps'];
        $warmup = $_POST['warmup'];

        $result = createWorkoutLogItemEntry($conn, $workoutLogId, $exerciseType, $exerciseId, $exerciseTime, $exerciseReps, $warmup);
        echo json_encode($result);
    }
}
?>
