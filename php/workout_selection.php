<?php
require_once 'db_connect.php';
require_once 'db_query.php';
require_once 'db_post.php';

session_start();

if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    exit;
}

$user_id = $_SESSION['user_id'];
$workout_id = $_POST['workout_id'];
$selected = $_POST['selected'];

if ($selected == "true") {
    post($conn, "INSERT INTO user_selected_workouts (user_id, workout_id) VALUES (?, ?)", [$user_id, $workout_id]);
} else {
    post($conn, "DELETE FROM user_selected_workouts WHERE user_id = ? AND workout_id = ?", [$user_id, $workout_id]);
}
