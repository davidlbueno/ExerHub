<?php
$pageTitle = "Edit Workout Log";
include 'php/session.php';
require_once 'php/header.php';
require_once 'php/db_connect.php';
require_once 'php/db_query.php';

$logId = $_GET['log_id'];
$userId = $_SESSION['user_id'];
$is_admin = $_SESSION['is_admin'];

// Check if the user is authorized to view this log
$authQuery = "SELECT user_id FROM workout_logs WHERE id = $logId";
$authResult = query($conn, $authQuery);
$authRow = mysqli_fetch_assoc($authResult);

if ($authRow['user_id'] !== $userId && !$is_admin) {
  echo "You can only view logs for your own workouts.";
  exit;
}

?>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>

<body class="dark">
  <main class="container">
    <?php
    // Fetch existing log entries for this logId
    $logItemsQuery = "SELECT * FROM workout_log_items WHERE workout_log_id = $logId";
    $logItemsResult = query($conn, $logItemsQuery);

    echo "<form action='update_log.php' method='post'>";
    echo "<input type='hidden' name='log_id' value='$logId'>";

    echo "<table>";
    echo "<tr><th>Exercise Type</th><th>Exercise Name</th><th>Time</th><th>Reps</th></tr>";

    while ($logItemRow = mysqli_fetch_assoc($logItemsResult)) {
      $exerciseType = $logItemRow['exercise_type'];
      $exerciseId = $logItemRow['exercise_id'];
      $exerciseTime = $logItemRow['exercise_time'];
      $reps = $logItemRow['reps'];

      // Fetch the exercise name based on the exerciseId
      if ($exerciseType === "Rest") {
        $exerciseName = "Rest";
      } else {
        if (isset($exerciseId) && !empty($exerciseId)) {
            $exerciseQuery = "SELECT name FROM exercises WHERE id = $exerciseId";
            $exerciseResult = query($conn, $exerciseQuery);
            $exerciseRow = mysqli_fetch_assoc($exerciseResult);
            $exerciseName = $exerciseRow['name'];
        } else {
            $exerciseName = "Unknown";
        }
      }

      // Determine the background color based on exercise type
      $bgColor = "";
      if ($exerciseType === "Rest") {
        $bgColor = "style='background-color: darkgreen;'";
      } elseif ($exerciseType === "Warmup") {
        $bgColor = "style='background-color: darkblue;'";
      }

      echo "<tr $bgColor>";
      echo "<td><input type='text' name='exercise_type[]' value='$exerciseType'></td>";
      echo "<td><input type='text' name='exercise_name[]' value='$exerciseName'></td>";  // Display the exercise name
      echo "<td><input type='text' name='exercise_time[]' value='$exerciseTime'></td>";
      echo "<td><input type='text' name='reps[]' value='$reps'></td>";
      echo "</tr>";
    }

    echo "</table>";
    echo "<input type='submit' value='Update Log'>";
    echo "</form>";
    ?>
  </main>
</body>
</html>
