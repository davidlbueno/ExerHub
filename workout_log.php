<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <title>BWE - Workout Log</title>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"></script>
  <link rel="stylesheet" href="style.css">
  <?php require_once 'php/db.php'; ?>
</head>
<body class="dark">
  <main class="container">
    <?php
    $logId = $_GET['log_id'];
    echo "<h4>Workout Log ID: $logId</h4>";

    // Retrieve the workout log items from the database
    $logItemsQuery = "SELECT exercise_type, exercise_id, exercise_time FROM workout_log_items WHERE workout_log_id = $logId";
    $logItemsResult = query($logItemsQuery);

    // Initialize total work time
    $totalWorkTime = 0;

    // Display the table of workout log items
    echo "<table>";
    echo "<thead><tr><th>Name</th><th>Type</th><th>Seconds</th><th>Muscles and Intensity</th></tr></thead>";
    echo "<tbody>";

    while ($logItemRow = mysqli_fetch_assoc($logItemsResult)) {
      $exerciseType = $logItemRow['exercise_type'];
      $exerciseId = $logItemRow['exercise_id'];
      $exerciseTime = $logItemRow['exercise_time'];

      if ($exerciseType === 'Rest') {
        // Handle Rest type items without exercise IDs
        $exerciseName = 'Rest';
        $rowClass = 'rest';
        $musclesIntensities = '-';
      } else {
        $rowClass = ($exerciseType === 'Warmup') ? 'warmup' : '';

        $exerciseQuery = "SELECT name, type FROM exercises WHERE id = $exerciseId";
        $exerciseResult = query($exerciseQuery);
        $exerciseRow = mysqli_fetch_assoc($exerciseResult);
        $exerciseName = $exerciseRow['name'];
        $exerciseType = $exerciseRow['type'];

        // Retrieve intensity and muscles worked for the exercise
        $exerciseMusclesQuery = "SELECT intensity, muscles.name FROM exercise_muscles JOIN muscles ON exercise_muscles.muscle_id = muscles.id WHERE exercise_id = $exerciseId";
        $exerciseMusclesResult = query($exerciseMusclesQuery);

        $musclesIntensities = '';

        while ($muscleRow = mysqli_fetch_assoc($exerciseMusclesResult)) {
          $intensity = $muscleRow['intensity'];
          $muscleName = $muscleRow['name'];
          $musclesIntensities .= "$muscleName ($intensity), ";
        }

        $musclesIntensities = rtrim($musclesIntensities, ', ');

        // Calculate total work time
        $totalWorkTime += $exerciseTime;
      }

      echo "<tr class='$rowClass'>";
      echo "<td>$exerciseName</td>";
      echo "<td>$exerciseType</td>";
      echo "<td>$exerciseTime</td>";
      echo "<td>$musclesIntensities</td>";
      echo "</tr>";
    }

    // Display the bottom row with total work time
    echo "<tfoot>";
    echo "<tr>";
    echo "<td colspan='3' style='text-align: right;'><strong>Total Work Time:</strong></td>";
    echo "<td colspan='2'><strong>$totalWorkTime</strong> seconds</td>";
    echo "</tr>";
    echo "</tfoot>";

    echo "</tbody>";
    echo "</table>";
    ?>
    <a href="#" id="closeBtn" class="close-btn">
      <i class="material-icons">close</i>
    </a>
  </main>
  <script>
    document.getElementById('closeBtn').href = document.referrer;
  </script>
</body>
</html>
