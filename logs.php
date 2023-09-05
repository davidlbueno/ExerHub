<?php
$pageTitle = "Logs";
include 'php/session.php';
require_once 'php/db_connect.php';
require_once 'php/db_query.php';
if (!isset($_SESSION['user_id'])) {
  header('Location: login.php');
  exit;
}
require_once 'php/header.php';
?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<body class="dark">
<?php include 'html/nav.html'; ?>
  <main class="container">
  <?php
    $userId = $_SESSION['user_id'];
    // print the user id to the console
    echo "<script>console.log('userId: $userId')</script>";

    // Retrieve the workout logs from the database for the user along with the workout name, sort newest to oldest
    $logsQuery = "SELECT workout_logs.id, workout_logs.start_time, workout_logs.end_time, workouts.name FROM workout_logs INNER JOIN workouts ON workout_logs.workout_id = workouts.id WHERE workout_logs.user_id = $userId ORDER BY workout_logs.start_time DESC";
    $logsResult = query($conn, $logsQuery);

    // Display the table of workout logs
    echo "<table>";
    echo "<thead><tr><th>Workout Name</th><th>Date</th><th>Length</th><th></th></tr></thead>";
    echo "<tbody>";
    while ($logRow = mysqli_fetch_assoc($logsResult)) {
      $logId = $logRow['id'];
      $startTime = $logRow['start_time'];
      $endTime = $logRow['end_time'];
      $workoutName = $logRow['name'];

      // Get the day name of the start time
      $dayName = date("l", strtotime($startTime));
      $formattedStartTime = date("Y-m-d H:i:s", strtotime($startTime));

      // Calculate the duration of the workout
      $duration = strtotime($endTime) - strtotime($startTime);
      $length = gmdate("H:i:s", $duration);

      echo "<tr>";
      echo "<td><a href='workout_log.php?log_id=$logId'>$workoutName</a></td>";
      echo "<td><a href='workout_log.php?log_id=$logId'>$dayName, $formattedStartTime</a></td>";
      echo "<td><a href='workout_log.php?log_id=$logId'>$length</a></td>";
      echo "<td><a href='#' class='delete-btn' data-log-id='$logId'><i class='material-icons'>delete</i></a></td>";
      echo "</tr>";
    }    
    echo "</tbody>";
    echo "</table>";
    ?>
  </main>
  <script src="js/nav.js"></script>
  <script>
    $(document).ready(function() {
      $('.delete-btn').click(function(e) {
        e.preventDefault();
        if (confirm('Are you sure you want to delete this workout log?')) {
          const logId = $(this).data('log-id');
          $.ajax({
            url: 'php/delete_workout_log.php',
            type: 'POST',
            data: JSON.stringify({ log_id: logId }),
            success: function(data) {
              console.log(data);
              location.reload();
            }
          });
        }
      });
    });
  </script>
  <?php include 'html/footer.html'; ?>
</body>
</html>
