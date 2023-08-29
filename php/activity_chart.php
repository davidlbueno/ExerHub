<?php
$logsQuery = "SELECT id, start_time, end_time, workout_id FROM workout_logs WHERE user_id = $userId";
$logsResult = query($conn, $logsQuery);

$workoutData = [];

while ($logRow = mysqli_fetch_assoc($logsResult)) {
    $workoutId = $logRow['workout_id'];
    $startTime = $logRow['start_time'];
    $endTime = $logRow['end_time'];

    $duration = strtotime($endTime) - strtotime($startTime);
    $avgDifficulty = getDifficulty($workoutId);

    $height = $duration * $avgDifficulty;

    $day = date("Y-m-d", strtotime($startTime));
    $workoutData[$day][] = ['height' => $height, 'workoutId' => $workoutId];
}

// Sort the array by day
ksort($workoutData);

// Convert PHP array to JavaScript object
$workoutDataJson = json_encode($workoutData);

function getDifficulty($workoutId) {
    global $conn;
    $query = "SELECT exercise_id FROM workout_sequences WHERE workout_id = $workoutId";
    $result = query($conn, $query);
    $row = mysqli_fetch_assoc($result);
    $exerciseId = $row['exercise_id'];

    $query = "SELECT difficulty FROM exercises WHERE id = $exerciseId";
    $result = query($conn, $query);
    $row = mysqli_fetch_assoc($result);
    $difficulty = $row['difficulty'];

    return $difficulty;
}

?>

<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.7.2/Chart.min.js"></script>

<div class="chart-container" style="width: 80%; margin-left: 10%; margin-top: 5px;">
    <div>
        <div>
            <canvas id="myChart" width="50" height="10"></canvas>
        </div>
        <div>
            <button type="button" class="btn btn-default">Back</button>
        </div>
    </div>
</div>

<script>
  var workoutData = <?php echo $workoutDataJson; ?>;
  var labels = Object.keys(workoutData);

  // Initialize data arrays for each workout type
  var cardioData = [];
  var strengthData = [];

  // Populate data arrays
  labels.forEach(function(label) {
      var dayData = workoutData[label];
      var cardioHeight = 0;
      var strengthHeight = 0;

      dayData.forEach(function(workout) {
          if (workout.workoutId === 'Cardio') {  // Replace with actual workout ID or type
              cardioHeight += workout.height;
          } else if (workout.workoutId === 'Strength') {  // Replace with actual workout ID or type
              strengthHeight += workout.height;
          }
      });

      cardioData.push(cardioHeight);
      strengthData.push(strengthHeight);
  });

  // Create the datasets array
  var datasets = [
      {
          label: 'Cardio',
          data: cardioData,
          backgroundColor: 'rgba(255, 99, 132, 0.2)'
      },
      {
          label: 'Strength',
          data: strengthData,
          backgroundColor: 'rgba(54, 162, 235, 0.2)'
      }
  ];

  // Create the chart
  var ctx = document.getElementById("myChart");
  var myChart = new Chart(ctx, {
      type: 'bar',
      data: {
          labels: labels,
          datasets: datasets
      },
      options: {
          scales: {
              x: {
                  stacked: true,
              },
              y: {
                  stacked: true
              }
          }
      }
  });
  </script>
