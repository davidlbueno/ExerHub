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
    $workoutData[$day][] = [
      'time' => $startTime,
      'duration' => $duration,
      'difficulty' => $avgDifficulty,
      'height' => $height, 
      'workoutId' => $workoutId, 
      'workoutName' => getWorkoutName($workoutId),  // Assuming you have a function to get the workout name
      'workoutLogURL' => "workout_log.php?id={$logRow['id']}"  // Assuming the URL structure
    ];
}

// Sort the array by day
ksort($workoutData);

// Convert PHP array to JavaScript object
$workoutDataJson = json_encode($workoutData);

// Function to get the workout name
function getWorkoutName($workoutId) {
    global $conn;
    $query = "SELECT name FROM workouts WHERE id = $workoutId";
    $result = query($conn, $query);
    $row = mysqli_fetch_assoc($result);
    $name = $row['name'];
    return $name;
}

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

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-date-fns"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-zoom"></script>

<style>
  /* Responsive chart container */
  .chart-container {
    width: 90%; /* Full width */
    min-height: 200px; /* Specified height */
    max-height: 300px; /* Maximum height */
    margin-top: 5px;
    margin-left: 5%;
    position: relative; /* If you want to position text or anything else inside */
  }
</style>

<div class="chart-container">
  <canvas id="myChart"></canvas>
  <button id="prevButton" type="button" class="btn btn-default">Previous</button>
  <button id="nextButton" type="button" class="btn btn-default">Next</button>
</div>

<!-- echo all retrieved workout data -->
<?php
  echo "<pre>";
  print_r($workoutData);
  echo "</pre>";
?>

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
for (var i = 0; i < allDates.length; i++) {
    var date = allDates[i];
    if (workoutData[date]) {
        var workouts = workoutData[date];
        var totalHeight = 0;
        for (var j = 0; j < workouts.length; j++) {
            totalHeight += workouts[j].height;
        }
        workoutHeights.push(totalHeight);
    } else {
        workoutHeights.push(0);
    }
}

// Limit to last 14 days initially
var initialDates = allDates.slice(-14);
var initialHeights = workoutHeights.slice(-14);

// Create the datasets array
var datasets = [
  {
    data: initialHeights,
    backgroundColor: 'rgba(75, 192, 192, 0.2)',
    xAxisID: 'x'  // Specify which x-axis to use
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
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
      legend: {
        display: false
      },
      zoom: {
        pan: {
          enabled: true,
          mode: 'x'
        },
        zoom: {
          enabled: true,
          mode: 'x'
        }
      },
    // Display WorkoutName, Time and Duration on hover
    tooltips: {
      callbacks: {
        title: function(tooltipItem) {
          var workout = workoutData[tooltipItem[0].label][0];
          return workout.workoutName;
        },
        label: function(tooltipItem) {
          var workout = workoutData[tooltipItem.label][0];
          var time = new Date(workout.time).toLocaleTimeString('en-US', { hour: 'numeric', minute: 'numeric' });
          var duration = Math.round(workout.duration / 60);
          return time + ' (' + duration + ' min)';
        },
        afterLabel: function(tooltipItem) {
          var workout = workoutData[tooltipItem.label][0];
          return 'Difficulty: ' + workout.difficulty;
        }
      }
    }
    },
    // when a user clicks on a bar, open the workout log url
    onClick: function(e) {
      var element = myChart.getElementAtEvent(e);
      if (element.length > 0) {
        var workout = workoutData[element[0].label][0];
        window.open(workout.workoutLogURL);
      }
    },  
    scales: {
      x: {
        type: 'time',
        position: 'bottom',
        time: {
          unit: 'day'
        },
        stacked: true
      },
      x1: {
        type: 'time',
        position: 'top',
        time: {
          unit: 'day',
          displayFormats: {
            day: 'ddd'
          }
        },
        ticks: {
          callback: function(value, index, values) {
            return new Date(value).toLocaleDateString('en-US', { weekday: 'short' });
          }
        }
      },
      y: {
        stacked: true,
        display: false
      }
    }
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

// Function to set the chart height
function setChartHeight() {
  var windowHeight = window.innerHeight;
  var chartHeight = windowHeight * 0.2; // 20% of the window height
  document.querySelector('.chart-container').style.height = chartHeight + 'px';
}

// Set the initial chart height
setChartHeight();

// Update the chart height whenever the window is resized
window.addEventListener('resize', setChartHeight);

// Initialize the chart with the last 14 days
updateChart(currentIndex, currentIndex + 14);

</script>
