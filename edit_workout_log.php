<?php
$pageTitle = "Edit Workout Log";
include 'php/session.php';
require_once 'php/header.php';
require_once 'php/db_connect.php';
require_once 'php/db_query.php';
?>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>

<body class="dark">
  <main class="container">
    <?php
    $logId = $_GET['log_id'];

    // Fetch existing log entries for this logId
    $logItemsQuery = "SELECT * FROM workout_log_items WHERE workout_log_id = $logId";
    $logItemsResult = query($conn, $logItemsQuery);

    echo "<form action='update_log.php' method='post'>";
    echo "<input type='hidden' name='log_id' value='$logId'>";

    echo "<table>";
    echo "<tr><th>Exercise Type</th><th>Exercise ID</th><th>Time</th><th>Reps</th><th>Warmup</th></tr>";

    while ($logItemRow = mysqli_fetch_assoc($logItemsResult)) {
      $exerciseType = $logItemRow['exercise_type'];
      $exerciseId = $logItemRow['exercise_id'];
      $exerciseTime = $logItemRow['exercise_time'];
      $reps = $logItemRow['reps'];
      $warmup = $logItemRow['warmup'];

      echo "<tr>";
      echo "<td><input type='text' name='exercise_type[]' value='$exerciseType'></td>";
      echo "<td><input type='text' name='exercise_id[]' value='$exerciseId'></td>";
      echo "<td><input type='text' name='exercise_time[]' value='$exerciseTime'></td>";
      echo "<td><input type='text' name='reps[]' value='$reps'></td>";
      echo "<td><input type='checkbox' name='warmup[]' value='1' " . ($warmup ? "checked" : "") . "></td>";
      echo "</tr>";
    }

    echo "</table>";
    echo "<input type='submit' value='Update Log'>";
    echo "</form>";
    ?>
  </main>
</body>
</html>
