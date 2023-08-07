<?php
  require_once 'php/db_connect.php';
  require_once 'php/db_query.php';
  if (isset($_SESSION['user_id'])) {
      $userId = $_SESSION['user_id'];
      $workouts = fetchWorkouts($userId);
  } else {
      $workouts = fetchWorkouts(null);
  }

  function fetchWorkouts($userId, $workoutId = null) {
    global $conn;
    if ($userId) {
      // Fetch the selected workouts for the logged-in user
      $query = "SELECT workouts.*, ROUND(AVG(exercises.difficulty)) as avg_difficulty 
                FROM workouts 
                INNER JOIN user_selected_workouts ON workouts.id = user_selected_workouts.workout_id
                LEFT JOIN workout_sequences ON workouts.id = workout_sequences.workout_id 
                LEFT JOIN exercises ON workout_sequences.exercise_id = exercises.id 
                WHERE user_selected_workouts.user_id = $userId 
                GROUP BY workouts.id";
    } else {
      // Fetch the public workouts for the non-logged-in user
      $query = "SELECT workouts.*, ROUND(AVG(exercises.difficulty)) as avg_difficulty 
                FROM workouts 
                LEFT JOIN workout_sequences ON workouts.id = workout_sequences.workout_id 
                LEFT JOIN exercises ON workout_sequences.exercise_id = exercises.id 
                WHERE workouts.is_public = 1 
                GROUP BY workouts.id";
    }
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $result = $stmt->get_result();
    $workouts = array();
    while ($row = $result->fetch_assoc()) {
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
