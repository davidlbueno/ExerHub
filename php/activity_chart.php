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
<script src="https://cdnjs.cloudflare.com/ajax/libs/chartjs-plugin-zoom/0.7.7/chartjs-plugin-zoom.min.js"></script>


<div class="chart-container" style="width: 80%; margin-left: 10%; margin-top: 5px;">
  <canvas id="myChart" width="50" height="10"></canvas>
</div>
<div>
  <button id="prevButton" type="button" class="btn btn-default">Previous</button>
  <button id="nextButton" type="button" class="btn btn-default">Next</button>
</div>

<script>
  var workoutData = <?php echo $workoutDataJson; ?>;
var labels = Object.keys(workoutData);

// Find the earliest and latest dates
var earliestDate = new Date(labels[0]);
var latestDate = new Date(labels[labels.length - 1]);

// Generate an array of all dates between earliest and latest
var allDates = [];
for (var d = new Date(earliestDate); d <= latestDate; d.setDate(d.getDate() + 1)) {
    allDates.push(new Date(d).toISOString().split('T')[0]);
}

// Initialize data arrays
var workoutHeights = [];

// Populate data arrays
allDates.forEach(function(label) {
    var totalHeight = 0;
    if (workoutData[label]) {
        workoutData[label].forEach(function(workout) {
            totalHeight += workout.height;
        });
    }
    workoutHeights.push(totalHeight);
});

// Limit to last 14 days initially
var initialDates = allDates.slice(-14);
var initialHeights = workoutHeights.slice(-14);

// Create the datasets array
var datasets = [
    {
        data: initialHeights,
        backgroundColor: 'rgba(75, 192, 192, 0.2)'
    }
];

// Create the chart
var ctx = document.getElementById("myChart");
var myChart = new Chart(ctx, {
    type: 'bar',
    data: {
        labels: initialDates,
        datasets: datasets
    },
    options: {
      legend: {
        display: false
      },
      scales: {
          x: {
              stacked: true,
              type: 'time',
              time: {
                  unit: 'day'
              },
              ticks: {
                  autoSkip: false
              }
          },
          y: {
              stacked: true
          }
      },
      pan: {
          enabled: true,
          mode: 'x'
      },
      zoom: {
          enabled: true,
          mode: 'x',
      }
    }
});

// Add Chart.js zoom plugin
Chart.pluginService.register({
    beforeInit: function(chart) {
        chart.options.pan = {
            enabled: true,
            mode: 'x'
        };
        chart.options.zoom = {
            enabled: true,
            mode: 'x',
        };
    }
});

// Initialize variables to keep track of the current date range
var currentIndex = allDates.length - 14;

// Function to update the chart
function updateChart(startIndex, endIndex) {
    myChart.data.labels = allDates.slice(startIndex, endIndex);
    myChart.data.datasets[0].data = workoutHeights.slice(startIndex, endIndex);
    myChart.update();
}

// Add event listeners to the buttons
document.getElementById('prevButton').addEventListener('click', function() {
    if (currentIndex > 0) {
        currentIndex -= 1;  // Scroll by one day
        updateChart(currentIndex, currentIndex + 14);
    }
});

document.getElementById('nextButton').addEventListener('click', function() {
    // Check if the last date in the current window is the latest date
    var lastDateInWindow = new Date(allDates[currentIndex + 13]);
    if (lastDateInWindow < latestDate) {
        currentIndex += 1;  // Scroll by one day
        updateChart(currentIndex, currentIndex + 14);
    }
});

// Initialize the chart with the last 14 days
updateChart(currentIndex, currentIndex + 14);

</script>
