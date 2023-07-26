<?php
  require_once 'db.php';
  require_once 'php/get_workouts.php';
  session_start();
  if (isset($_SESSION['user_id'])) {
      $userId = $_SESSION['user_id'];
      $workouts = fetchWorkouts($userId);
  } else {
      $workouts = fetchWorkouts(null);
  }

  function fetchWorkouts($userId, $workoutId = null) {
    global $conn;
    if ($workoutId) {
        $query = "SELECT * FROM workout_sequences WHERE workout_id = $workoutId";
    } else {
        if ($userId) {
            $query = "SELECT workouts.*, ROUND(AVG(exercises.difficulty)) as avg_difficulty 
                      FROM workouts 
                      LEFT JOIN workout_sequences ON workouts.id = workout_sequences.workout_id 
                      LEFT JOIN exercises ON workout_sequences.exercise_id = exercises.id 
                      WHERE workouts.user_id = $userId 
                      GROUP BY workouts.id";
        } else { // Not sure this is necessary. We may want to use it to get a single workout, but IDK. 
            $query = "SELECT workouts.*, ROUND(AVG(exercises.difficulty)) as avg_difficulty 
                      FROM workouts 
                      LEFT JOIN workout_sequences ON workouts.id = workout_sequences.workout_id 
                      LEFT JOIN exercises ON workout_sequences.exercise_id = exercises.id 
                      WHERE workouts.is_public = 1 
                      GROUP BY workouts.id";
        }
    }
    $result = query($query);
    $workouts = array();
    while ($row = mysqli_fetch_assoc($result)) {
        $workouts[] = $row;
    }
    return $workouts;
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


