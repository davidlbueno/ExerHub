<?php
// Combine queries to fetch all required data in one go
$query = "SELECT wl.id, wl.start_time, wl.end_time, wl.workout_id, w.name as workout_name, e.difficulty
          FROM workout_logs wl
          JOIN workouts w ON wl.workout_id = w.id
          JOIN workout_sequences ws ON wl.workout_id = ws.workout_id
          JOIN exercises e ON ws.exercise_id = e.id
          WHERE wl.user_id = $userId
          ORDER BY wl.start_time ASC";

$result = query($conn, $query);

$workoutData = [];

while ($row = mysqli_fetch_assoc($result)) {
    $day = date("Y-m-d", strtotime($row['start_time']));
    $duration = strtotime($row['end_time']) - strtotime($row['start_time']);
    $height = $duration * $row['difficulty'];

    $workoutData[$day][] = [
        'time' => $row['start_time'],
        'duration' => $duration,
        'difficulty' => $row['difficulty'],
        'height' => $height,
        'workoutId' => $row['workout_id'],
        'workoutName' => $row['workout_name'],
        'workoutLogURL' => "workout_log.php?log_id={$row['id']}"
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
      tooltip: {
        callbacks: {
          title: function(tooltipItem) {
            if (tooltipItem[0] && tooltipItem[0].label) {
              var dateStr = tooltipItem[0].label.split(",")[0] + ", " + tooltipItem[0].label.split(",")[1].trim();
              var dateLabel = new Date(dateStr);
              if (isNaN(dateLabel.getTime())) {
                console.error("Invalid date:", tooltipItem[0].label);
                return "Invalid date";
              }
              dateLabel = dateLabel.toISOString().split('T')[0];
              var workoutForDay = workoutData[dateLabel];
              if (workoutForDay && workoutForDay.length > 0) {
                return workoutForDay.map(function(workout) {
                  return workout.workoutName + ' at ' + workout.time.split(' ')[1];
                }).join(', ');
              }
              return dateLabel;
            }
            return "No date available";
          },
          label: function(tooltipItem) {
            if (tooltipItem[0] && tooltipItem[0].label) {
              var dateStr = tooltipItem[0].label.split(",")[0] + ", " + tooltipItem[0].label.split(",")[1].trim();
              var dateLabel = new Date(dateStr);
              if (isNaN(dateLabel.getTime())) {
                console.error("Invalid date:", tooltipItem[0].label);
                return "Invalid date";
              }
              dateLabel = dateLabel.toISOString().split('T')[0];
              var workoutForDay = workoutData[dateLabel];
              if (workoutForDay && workoutForDay.length > 0) {
                return workoutForDay.map(function(workout) {
                  return 'Duration: ' + workout.duration + ' seconds';
                }).join(', ');
              }
              return '';
            }
            return "No data available";
          }
        }
      }
    },
    // when a user clicks on a bar, open the workout log url
    onClick: function(evt) {
      var activePoints = myChart.getElementsAtEventForMode(evt, 'nearest', { intersect: true }, true);
      var firstPoint = activePoints[0];
      if (firstPoint) {
        var label = myChart.data.labels[firstPoint.index];
        var dateLabel = new Date(label).toISOString().split('T')[0];
        var workoutDay = workoutData[dateLabel];
        if (workoutDay && workoutDay.length > 0) {
          var firstWorkout = workoutDay[0];
          if (firstWorkout && firstWorkout.workoutLogURL) {
            window.location.href = firstWorkout.workoutLogURL;
          }
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
