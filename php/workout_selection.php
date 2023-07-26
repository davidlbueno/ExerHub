<?php
require 'php/db.php';

session_start();

if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    exit;
}

$user_id = $_SESSION['user_id'];
$workout_id = $_POST['workout_id'];
$selected = $_POST['selected'];

if ($selected) {
    query("INSERT INTO user_selected_workouts (user_id, workout_id) VALUES ($user_id, $workout_id)");
} else {
    query("DELETE FROM user_selected_workouts WHERE user_id = $user_id AND workout_id = $workout_id");
}
