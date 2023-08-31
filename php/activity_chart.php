<?php
// Combine queries to fetch all required data in one go
$query = "SELECT wl.id, wl.start_time, wl.end_time, wl.workout_id, w.name as workout_name, ROUND(AVG(e.difficulty)) as avg_difficulty, GROUP_CONCAT(e.type) as exercise_types
          FROM workout_logs wl
          JOIN workouts w ON wl.workout_id = w.id
          JOIN workout_sequences ws ON wl.workout_id = ws.workout_id
          JOIN exercises e ON ws.exercise_id = e.id
          WHERE wl.user_id = $userId AND e.type NOT IN ('Rest', 'Warmup')
          GROUP BY wl.id
          ORDER BY wl.start_time ASC";

$result = query($conn, $query);

$workoutData = [];

while ($row = mysqli_fetch_assoc($result)) {
  $day = date("Y-m-d", strtotime($row['start_time']));
  $duration = strtotime($row['end_time']) - strtotime($row['start_time']);
  $intensity = $duration * $row['avg_difficulty'];

  // Determine workout_type based on exercise_types
  $exercise_types = explode(",", $row['exercise_types']);
  $unique_types = array_unique($exercise_types);
  $workout_type = "Mixed";
  if (count($unique_types) === 1) {
      $workout_type = $unique_types[0];
  }

  $workoutData[$day][] = [
      'time' => $row['start_time'],
      'duration' => $duration,
      'difficulty' => $row['avg_difficulty'],
      'intensity' => $intensity,
      'workoutId' => $row['workout_id'],
      'workoutName' => $row['workout_name'],
      'workoutLogURL' => "workout_log.php?log_id={$row['id']}",
      'workout_type' => $workout_type
  ];
}

ksort($workoutData);
$workoutDataJson = json_encode($workoutData);
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

<script>
var workoutData = <?php echo $workoutDataJson; ?>;
// write workoutData to the screen
document.write(JSON.stringify(workoutData));

var labels = Object.keys(workoutData);

// Find the earliest and latest dates
var earliestDate = new Date(labels[0]);
var latestDate = new Date(labels[labels.length - 1]);

// Generate an array of all dates between earliest and latest
var allDates = [];
for (var d = new Date(earliestDate); d <= latestDate; d.setDate(d.getDate() + 1)) {
    allDates.push(new Date(d).toISOString().split('T')[0]);
}

// Function to get color based on workout_type
function getColor(workout_type) {
  switch (workout_type) {
    case 'Push':
      return 'rgba(28, 123, 255, 0.61)';
    case 'Pull':
      return 'rgba(235, 54, 54, 0.6)';
    case 'Legs':
      return 'rgba(220, 160, 8, 0.6)';
    default:
      return 'rgba(145, 75, 192, 0.6)';
  }
}

// Create the datasets dynamically
var datasets = [];
for (var i = 0; i < allDates.length; i++) {
  var date = allDates[i];
  if (workoutData[date]) {
    var workouts = workoutData[date];
    for (var j = 0; j < workouts.length; j++) {
      var workout = workouts[j];
      datasets.push({
        label: workout.workoutName,
        data: [{ x: date, y: workout.intensity }],
        backgroundColor: getColor(workout.workout_type),
        workoutLogURL: workout.workoutLogURL  // Custom property for the URL
      });
    }
  }
}

// Create the chart
var ctx = document.getElementById("myChart");
var myChart = new Chart(ctx, {
  type: 'bar',
  data: {
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
      },
      tooltip: {
        callbacks: {
          label: function(tooltipItem, data) {
            var dataset = myChart.data.datasets[tooltipItem.datasetIndex];
            var workoutForDay = workoutData[tooltipItem.label];
            if (workoutForDay && workoutForDay.length > 0) {
              var workout = workoutForDay.find(w => w.workoutName === dataset.label);
              if (workout) {
                return [
                  'Workout: ' + workout.workoutName,
                  'Time: ' + new Date(workout.time).toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' }),
                  'Type: ' + workout.workout_type
                ];
              }
            }
            return '';
          }
        }
      }
    },
    onClick: function(evt) {
      var activePoints = myChart.getElementsAtEventForMode(evt, 'nearest', { intersect: true }, true);
      var firstPoint = activePoints[0];
      if (firstPoint) {
        var dataset = myChart.data.datasets[firstPoint.datasetIndex];
        if (dataset && dataset.workoutLogURL) {
          window.location.href = dataset.workoutLogURL;
        }
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
        display: true
      }
    }
  }
});

// Initialize variables to keep track of the current date range
var currentIndex = allDates.length - 14;

// Function to update the chart
function updateChart(startIndex, endIndex) {
    myChart.data.datasets[0].data = workoutIntensities.slice(startIndex, endIndex);
    myChart.update();
}

// Add event listeners to the buttons
document.getElementById('prevButton').addEventListener('click', function() {
    if (currentIndex > 0) {
        currentIndex -= 1;
        updateChart(currentIndex, currentIndex + 14);
    }
});

document.getElementById('nextButton').addEventListener('click', function() {
    var lastDateInWindow = new Date(allDates[currentIndex + 13]);
    if (lastDateInWindow < latestDate) {
        currentIndex += 1;
        updateChart(currentIndex, currentIndex + 14);
    }
});

// Function to set the chart height
function setChartHeight() {
  var windowHeight = window.innerHeight;
  var chartHeight = windowHeight * 0.8;
  document.getElementById('myChart').height = chartHeight;
}

// Set the initial chart height
setChartHeight();

// Update the chart height when the window is resized
window.addEventListener('resize', setChartHeight);

</script>
