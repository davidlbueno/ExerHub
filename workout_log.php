<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
  <?php include 'php/header.php'; ?>
  <title>ExerHub - Workout Log</title>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.5.0/chart.min.js"></script>
  <link rel="stylesheet" href="style.css">
  <?php require_once 'php/db.php'; ?>
</head>
<body class="dark">
  <main class="container">
    <?php
    $logId = $_GET['log_id'];

    $workoutNameQuery = "SELECT workouts.name FROM workout_logs INNER JOIN workouts ON workout_logs.workout_id = workouts.id WHERE workout_logs.id = $logId";
    $workoutNameResult = query($workoutNameQuery);
    $workoutNameRow = mysqli_fetch_assoc($workoutNameResult);
    $workoutName = $workoutNameRow['name'];

    $startTimeQuery = "SELECT start_time FROM workout_logs WHERE id = $logId";
    $startTimeResult = query($startTimeQuery);
    $startTimeRow = mysqli_fetch_assoc($startTimeResult);
    $startTime = $startTimeRow['start_time'];

    echo "<div class='row'>";
    echo "<div class='col s6'>";    
    echo "<h4>$workoutName</h4>
          </div>
          </div>
          <div style='text-align: right;'> Workout Date: $startTime</div>";

    // Retrieve the workout log items from the database
    $logItemsQuery = "SELECT exercise_type, exercise_id, exercise_time, reps, warmup FROM workout_log_items WHERE workout_log_id = $logId";
    $logItemsResult = query($logItemsQuery);

    $totalWorkTime = 0;
    $graphData = [];
    $prevGraphData = [];

    echo "<table>";
    echo "<thead><tr><th>Name</th><th>Type</th><th>Seconds</th><th>Difficulty</th><th>Reps</th><th>Muscles and Intensity</th></tr></thead>";
    echo "<tbody>";

    while ($logItemRow = mysqli_fetch_assoc($logItemsResult)) {
      $exerciseType = $logItemRow['exercise_type'];
      $exerciseId = $logItemRow['exercise_id'];
      $exerciseTime = $logItemRow['exercise_time'];
      $reps = $logItemRow['reps'];
      $warmup = $logItemRow['warmup'];

      // Rest items handling
      if ($exerciseType === 'Rest') {
        $rowClass = 'rest';
        $exerciseName = 'Rest';
        $exerciseType = 'Rest';
        $difficulty = '-';
        $musclesIntensities = '-';
      } else {
        $rowClass = ($warmup === '1') ? 'warmup' : '';

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

        $musclesIntensities = rtrim($musclesIntensities, ', ');

        $graphData[] = [
          'name' => $exerciseName,
          'reps' => $reps,
          'duration' => (int) $exerciseTime,
        ];
      }

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

    echo "<tfoot>";
    echo "<tr>";
    echo "<td colspan='3' style='text-align: right;'><strong>Total Work Time:</strong></td>";
    echo "<td colspan='2'><strong>$totalWorkTime</strong> seconds</td>";
    echo "</tr>";
    echo "</tfoot>";

    echo "</tbody>";
    echo "</table>";

    $workoutIdQuery = "SELECT workout_id FROM workout_logs WHERE id = $logId";
    $workoutIdResult = query($workoutIdQuery);
    $workoutIdRow = mysqli_fetch_assoc($workoutIdResult);
    $workoutId = $workoutIdRow['workout_id'];
    $prevLogIdQuery = "SELECT id FROM workout_logs WHERE workout_id = $workoutId AND id < $logId ORDER BY id DESC LIMIT 1";
    $prevLogIdResult = query($prevLogIdQuery);
    $prevLogIdRow = mysqli_fetch_assoc($prevLogIdResult);
    
    if ($prevLogIdRow) {
      $prevLogId = $prevLogIdRow['id'];
      echo "<script>console.log('Previous Log ID: $prevLogId');</script>";
      $prevStartTimeQuery = "SELECT start_time FROM workout_logs WHERE id = $prevLogId";
      $prevStartTimeResult = query($prevStartTimeQuery);
      $prevStartTimeRow = mysqli_fetch_assoc($prevStartTimeResult);
      $prevStartTime = $prevStartTimeRow['start_time'];
      
      echo "<div class='row'>";
      echo "<div class='col s6'>";    
      echo "<h4>Previous</h4></div></div>";
      echo "<div style='text-align: right;'> Workout Date: $prevStartTime</div>";

      $prevLogItemsQuery = "SELECT exercise_type, exercise_id, exercise_time, reps, warmup FROM workout_log_items WHERE workout_log_id = $prevLogId";
      $prevLogItemsResult = query($prevLogItemsQuery);

      echo "<table>";
      echo "<thead><tr><th>Name</th><th>Type</th><th>Seconds</th><th>Difficulty</th><th>Reps</th><th>Muscles and Intensity</th></tr></thead>";
      echo "<tbody>";

      while ($prevLogItemRow = mysqli_fetch_assoc($prevLogItemsResult)) {
        $exerciseType = $prevLogItemRow['exercise_type'];
        $exerciseTime = $prevLogItemRow['exercise_time'];

        if ($exerciseType === 'Rest') {
          $rowClass = 'rest';
          $exerciseName = 'Rest';
          $exerciseType = 'Rest';
          $difficulty = '-';
          $musclesIntensities = '-';
        } else {
          $exerciseId = $prevLogItemRow['exercise_id'];
          $reps = $prevLogItemRow['reps'];
          $warmup = $prevLogItemRow['warmup'];
          $rowClass = ($warmup === '1') ? 'warmup' : '';

          $exerciseQuery = "SELECT name, type, difficulty FROM exercises WHERE id = $exerciseId";
          $exerciseResult = query($exerciseQuery);
          $exerciseRow = mysqli_fetch_assoc($exerciseResult);
          $exerciseName = $exerciseRow['name'];
          $exerciseType = $exerciseRow['type'];
          $difficulty = $exerciseRow['difficulty'];

          $exerciseMusclesQuery = "SELECT intensity, muscles.name FROM exercise_muscles JOIN muscles ON exercise_muscles.muscle_id = muscles.id WHERE exercise_id = $exerciseId";
          $exerciseMusclesResult = query($exerciseMusclesQuery);
          $musclesIntensities = '';

          while ($muscleRow = mysqli_fetch_assoc($exerciseMusclesResult)) {
            $intensity = $muscleRow['intensity'];
            $muscleName = $muscleRow['name'];
            $musclesIntensities .= "$muscleName ($intensity), ";
          }

          $musclesIntensities = rtrim($musclesIntensities, ', ');

          if ($exerciseType !== 'Rest') {
            $prevGraphData[] = [
              'name' => $exerciseName,
              'reps' => $reps,
              'duration' => (int) $exerciseTime,
            ];
          }
        }

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
      echo "<tfoot>";
      echo "<tr>";
      echo "<td colspan='3' style='text-align: right;'><strong>Total Work Time:</strong></td>";
      echo "<td colspan='2'><strong>$totalWorkTime</strong> seconds</td>";
      echo "</tr>";
      echo "</tfoot>";
      echo "</tbody>";
      echo "</table>";
    } else {
      echo "<script>console.log('No previous log found.');</script>";
      echo "<h4>No Previous Workout Log Found</h4>";
    }

    // Render the graph
    echo "<canvas id='graphCanvas'></canvas>";
    // Prepare the graph data as JSON
    $graphDataJson = json_encode($graphData);
    $prevGraphDataJson = json_encode($prevGraphData);
    echo "<script>var graphData = $graphDataJson;</script>";
    echo "<script>var prevGraphData = $prevGraphDataJson;</script>";
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
