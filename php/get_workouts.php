<?php
require_once 'db.php';

function fetchWorkouts($userId, $workoutId) {
  global $conn;
  $workoutId = $workout['id'];
  $workoutName = $workout['name'];
  if ($workoutId) {
    $query = "SELECT * FROM workout_sequences WHERE workout_id = $workoutId";
  } else {
      $query = "SELECT * FROM workouts WHERE user_id = $userId";
  }
  $result = query($query);
  ;
  
  $workouts = array();
  while ($row = mysqli_fetch_assoc($result)) {
    $workouts[] = $row;
  }
  
  return $workouts;
}

function displayWorkouts($workouts) {
  echo '<ul>';
  foreach ($workouts as $workout) {
    $workoutId = $workout['id'];
    $workoutName = $workout['name'];
    echo '<li><a href="workout_details.php?id=' . $workoutId . '">' . $workoutName . '</a></li>';
  }
  echo '</ul>';
}

function displayWorkout($workout) {
  echo '<ul>';
  foreach ($items as $item) {
    $type = $workout['type'];
    $exercise = $workout['name'];
    $seconds = $workout['seconds'];
    $sets = $workout['sets'];
    echo '<li><a href="workout_details.php?id=' . $type . '">' . $seconds . '</a></li>';
  }
  echo '</ul>';
}

?>


