<!-- save_workout.php -->
<?php
// Include the database connection functions
require_once('db.php');

// Get the workout name from the form data
$workout_name = mysqli_real_escape_string(connect(), $_POST['workout_name']);

// Insert the workout into the database
$sql = "INSERT INTO workouts (name, user_id, is_public) VALUES ('$workout_name', 1, 1)";
execute_query($sql);
$workout_id = mysqli_insert_id(connect());

// Loop through the workout sequence list and insert each exercise into the database
