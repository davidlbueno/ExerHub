<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <title>BWE - Workout Log</title>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <link rel="stylesheet" href="style.css">
  <?php require_once 'php/db.php'; ?>
</head>
<body class="dark">
  <main class="container">
    <?php
    $logId = $_GET['log_id'];
    echo "<h4>Workout Log ID: $logId</h4>";

    // Retrieve the workout log items from the database
    $logItemsQuery = "SELECT exercise_type, exercise_id, exercise_time, reps FROM workout_log_items WHERE workout_log_id = $logId";
    $logItemsResult = query($logItemsQuery);

    // Initialize total work time and data arrays for the graph
    $totalWorkTime = 0;
    $graphData = [];

    // Display the table of workout log items
    echo "<table>";
    echo "<thead><tr><th>Name</th><th>Type</th><th>Seconds</th><th>Difficulty</th><th>Reps</th><th>Muscles and Intensity</th></tr></thead>";
    echo "<tbody>";

    while ($logItemRow = mysqli_fetch_assoc($logItemsResult)) {
      $exerciseType = $logItemRow['exercise_type'];
      $exerciseId = $logItemRow['exercise_id'];
      $exerciseTime = $logItemRow['exercise_time'];
      $reps = $logItemRow['reps'];
    
      // Rest items handling
      if ($exerciseType === 'Rest') {
        $exerciseName = 'Rest';
        $rowClass = 'rest';
        $musclesIntensities = '-';
        $difficulty = '-';
        $reps = '-'; // Set reps as hyphen for Rest items
      } else {
        $rowClass = ($exerciseType === 'Warmup') ? 'warmup' : '';
    
        $exerciseQuery = "SELECT name, type, difficulty FROM exercises WHERE id = $exerciseId";
        $exerciseResult = query($exerciseQuery);
        $exerciseRow = mysqli_fetch_assoc($exerciseResult);
        $exerciseName = $exerciseRow['name'];
        $exerciseType = $exerciseRow['type'];
        $difficulty = $exerciseRow['difficulty'];
    
        // Retrieve intensity and muscles worked for the exercise
        $exerciseMusclesQuery = "SELECT intensity, muscles.name FROM exercise_muscles JOIN muscles ON exercise_muscles.muscle_id = muscles.id WHERE exercise_id = $exerciseId";
        $exerciseMusclesResult = query($exerciseMusclesQuery);
    
        $musclesIntensities = '';
    
        while ($muscleRow = mysqli_fetch_assoc($exerciseMusclesResult)) {
          $intensity = $muscleRow['intensity'];
          $muscleName = $muscleRow['name'];
          $musclesIntensities .= "$muscleName ($intensity), ";
        }
      }
    
      $musclesIntensities = rtrim($musclesIntensities, ', ');
    
      $graphData[] = [
        'name' => $exerciseName,
        'reps' => $reps,
        'duration' => (int) $exerciseTime,
      ];
    
      // Calculate total work time and add data to the graph array
      $totalWorkTime += $exerciseTime;
    
      echo "<tr class='$rowClass'>";
      echo "<td>$exerciseName</td>";
      echo "<td>$exerciseType</td>";
      echo "<td>$exerciseTime</td>";
      echo "<td>$difficulty</td>";
      echo "<td>$reps</td>";
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

    // Render the graph
    echo "<canvas id='graphCanvas'></canvas>";

    // Prepare the graph data as JSON
    $graphDataJson = json_encode($graphData);
    echo "<script>var graphData = $graphDataJson;</script>";
    echo "<script src='js/workout_graph.js'></script>";
    echo "<script>var totalWorkTime = $totalWorkTime;</script>";

    // Function to calculate the average intensity for an exercise
    function calculateAverageIntensity($exerciseId) {
      $exerciseMusclesQuery = "SELECT intensity FROM exercise_muscles WHERE exercise_id = $exerciseId";
      $exerciseMusclesResult = query($exerciseMusclesQuery);
      $intensities = [];

      while ($muscleRow = mysqli_fetch_assoc($exerciseMusclesResult)) {
        $intensity = $muscleRow['intensity'];
        $intensities[] = $intensity;
      }

      $averageIntensity = array_sum($intensities) / count($intensities);
      return round($averageIntensity, 2);
    }
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
