<?php
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
    $exercise_types = explode(",", $row['exercise_types']);
    $unique_types = array_unique($exercise_types);
    $workout_type = count($unique_types) === 1 ? $unique_types[0] : "Mixed";
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

$latestDate = date("Y-m-d");  // Get the current date
$workoutData[$latestDate] = $workoutData[$latestDate] ?? [];  // Ensure there's an entry for the current date
ksort($workoutData);  // Sort the array again
$workoutDataJson = json_encode($workoutData);  // Encode to JSON
?>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-date-fns"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-zoom"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/hammer.js/2.0.8/hammer.min.js"></script>

<style>
  /* Responsive chart container */
  .chart-container {
    width: 90%; /* Full width */
    height: 200px; /* Specified height */
    margin-top: 5px;
    margin-left: 5%;
    position: relative; /* If you want to position text or anything else inside */
  }

/* Style the scrollbar container */
input[type="range"] {
  -webkit-appearance: none; /* Override default appearance */
  width: 100%; /* Full-width */
  height: 10px; /* Specified height */
  background: #333; /* Dark background */
  outline: none; /* Remove outline */
  border: none; /* Remove border */
  border-radius: 0; /* No rounded corners */
  opacity: 1; /* No transparency */
  cursor: pointer; /* Cursor on hover */
}

/* Style the scrollbar thumb */
input[type="range"]::-webkit-slider-thumb {
  -webkit-appearance: none; /* Override default appearance */
  width: 15%; /* Set a specific thumb width */
  height: 10px; /* Specified height */
  background: #666; /* Grey thumb color */
  border: none; /* Remove border */
  border-radius: 0; /* No rounded corners */
  cursor: pointer; /* Cursor on hover */
}

input[type="range"]::-moz-range-thumb {
  width: 15%; /* Set a specific thumb width */
  height: 10px; /* Specified height */
  background: #666; /* Grey thumb color */
  border: none; /* Remove border */
  border-radius: 0; /* No rounded corners */
  cursor: pointer; /* Cursor on hover */
}

/* Remove the track */
input[type="range"]::-webkit-slider-runnable-track {
  background: none;
  border: none;
}

input[type="range"]::-moz-range-track {
  background: none;
  border: none;
}

.chart-container canvas {
  width: 100% !important;
  height: auto !important;
}

</style>

<div class="chart-container">
  <canvas id="myChart"></canvas>
  <input type="range" id="chartScrollbar" min="0" max="100" value="100">
</div>

<script>
var workoutData = <?php echo $workoutDataJson; ?>;
var labels = Object.keys(workoutData);
var earliestDate = new Date(labels[0]);
var latestDate = new Date(labels[labels.length - 1]);  // This will now include the current date
var allDates = [];

for (var d = new Date(earliestDate); d <= latestDate; d.setDate(d.getDate() + 1)) {
    allDates.push(new Date(d).toISOString().split('T')[0]);
}

function getColor(workout_type) {
  switch (workout_type) {
    case 'Push':
      return 'rgba(28, 123, 255, 0.61)';
    case 'Pull':
      return 'rgba(235, 54, 54, 0.6)';
    case 'Legs':
      return 'rgba(17, 245, 13, 0.6)';
    case 'Core':
      return 'rgba(236, 255, 27, 0.6)';
    case 'Mixed':
      return 'rgba(145, 75, 192, 0.6)';
    default:
      return 'rgba(145, 75, 192, 0.6)';
  }
}

var datasets = [];
var numDays = 14;
var startIndex = allDates.length - numDays;
if (startIndex < 0) {
    startIndex = 0;
}
var datesToDisplay = allDates.slice(startIndex);

function adjustColor(color, factor) {
    var [r, g, b, a] = color.match(/\d+/g);
    return `rgba(${Math.min(255, r * factor)}, ${Math.min(255, g * factor)}, ${Math.min(255, b * factor)}, ${a / 255})`;
}

for (var i = 0; i < datesToDisplay.length; i++) {
    var date = datesToDisplay[i];
    var workouts = workoutData[date] || [];
    if (workouts.length === 0) {
        datasets.push({
            label: '',
            data: [{ x: date, y: null }],
            backgroundColor: 'rgba(0, 0, 0, 0)'
        });
    } else {
        // Group workouts by type and sort them
        var groupedWorkouts = {};
        for (var j = 0; j < workouts.length; j++) {
            var workout = workouts[j];
            if (!groupedWorkouts[workout.workout_type]) {
                groupedWorkouts[workout.workout_type] = [];
            }
            groupedWorkouts[workout.workout_type].push(workout);
        }

        // Generate datasets
        for (var type in groupedWorkouts) {
            var typeWorkouts = groupedWorkouts[type];
            for (var k = 0; k < typeWorkouts.length; k++) {
                var workout = typeWorkouts[k];
                var baseColor = getColor(workout.workout_type);
                var adjustedColor = k % 2 === 0 ? baseColor : adjustColor(baseColor, 1.2);  // Adjust color for alternate bars

                datasets.push({
                    label: workout.workoutName,
                    data: [{ x: date, y: workout.intensity }],
                    backgroundColor: adjustedColor,
                    workoutType: workout.workout_type,
                    time: workout.time,
                    duration: workout.duration,
                    difficulty: workout.difficulty,
                    workoutLogURL: workout.workoutLogURL
                });
            }
        }
    }
}

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
      tooltip: {
        callbacks: {
            title: function(context) {
                var firstPoint = context[0];
                var dataset = firstPoint.dataset;
                return dataset.label;
            },
            label: function(context) {
                var type = context.dataset.workoutType;
                var time = new Date(context.dataset.time).toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });
                var duration = new Date(context.dataset.duration * 1000).toISOString().substr(11, 8);
                var difficulty = context.dataset.difficulty;

                return [
                    `Type: ${type}`,
                    `Time: ${time}`,
                    `Duration: ${duration}`,
                    `Difficulty: ${difficulty}`
                ];
            }
        }
    },
    zoom: {
        pan: {
          enabled: true,
          mode: 'x',
          onPan: function() { console.log('Panning'); }
        },
        zoom: {
          wheel: {
            enabled: false,
          },
          drag: {
            enabled: false,
          },
          pinch: {
            enabled: false,
          },
          onZoom: function() { console.log('Zooming'); }
        }
      },
      afterDraw: function(chart) {
        console.log("Executing afterDraw hook");  // To check if the hook is being executed
        var ctx = chart.ctx;
        var xAxis = chart.scales['x'];  // Changed to 'x' to match the time-based x-axis
        var chartArea = chart.chartArea;
        var currentDate = new Date().toISOString().split('T')[0];  // Get the current date in 'YYYY-MM-DD' format

        // Find the x-coordinate for the current date
        var xCoord = xAxis.getPixelForValue(currentDate);

        if (xCoord) {
          ctx.fillStyle = 'rgba(0, 255, 0, 0.5)';  // Changed to a green color for visibility
          ctx.fillRect(xCoord - xAxis.width / xAxis.ticks.length / 2, chartArea.top, xAxis.width / xAxis.ticks.length, chartArea.bottom - chartArea.top);
        }
      },
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
    type: 'category',
    position: 'top',
    labels: datesToDisplay.map(date => new Date(date + 'T00:00:00Z').toLocaleDateString('en-US', { weekday: 'short', timeZone: 'UTC' })),
  },
  y: {
    stacked: true,
    display: false
  }
}
}});

var currentIndex = allDates.length - 14;

// Initialize Hammer.js
var hammer = new Hammer(document.getElementById('myChart'));

// Handle pan events
hammer.on('panend', function(e) {
  // Determine the direction of the pan (left or right)
  var direction = e.direction;
  
  // Update the current index based on the direction and distance
  if (direction === Hammer.DIRECTION_LEFT) {
    currentIndex += Math.ceil(e.distance / 50); // Adjust the divisor as needed
  } else if (direction === Hammer.DIRECTION_RIGHT) {
    currentIndex -= Math.ceil(e.distance / 50); // Adjust the divisor as needed
  }

  // Boundary checks
  if (currentIndex < 0) {
    currentIndex = 0;
  } else if (currentIndex > allDates.length - numDays) {
    currentIndex = allDates.length - numDays;
  }
  
  // Update the scrollbar
  scrollbar.value = currentIndex;  // <-- Add this line
  
  // Update the chart
  updateChart(currentIndex, currentIndex + numDays);
});

// Function to calculate the number of dates to display based on window width
function calculateNumDates() {
  var windowWidth = window.innerWidth;
  return Math.floor(windowWidth / 50); // Adjust the divisor as needed
}

// Update the updateChart function to take numDays as an argument
function updateChart(startIndex, endIndex, numDays) {
    var datesToDisplay = allDates.slice(startIndex, endIndex);
    var datasets = [];
    for (var i = 0; i < datesToDisplay.length; i++) {
        var date = datesToDisplay[i];
        var workouts = workoutData[date] || [];
        if (workouts.length === 0) {
            datasets.push({
                label: '',
                data: [{ x: date, y: null }],
                backgroundColor: 'rgba(0, 0, 0, 0)'
            });
        } else {
            for (var j = 0; j < workouts.length; j++) {
                var workout = workouts[j];
                datasets.push({
                    label: workout.workoutName,
                    data: [{ x: date, y: workout.intensity }],
                    backgroundColor: getColor(workout.workout_type),
                    workoutType: workout.workout_type,
                    time: workout.time,
                    duration: workout.duration,
                    difficulty: workout.difficulty,
                    workoutLogURL: workout.workoutLogURL
                });
            }
        }
    }
    myChart.data.datasets = datasets;
    myChart.options.scales.x1.labels = datesToDisplay.map(date => new Date(date + 'T00:00:00Z').toLocaleDateString('en-US', { weekday: 'short', timeZone: 'UTC' }));
    myChart.update();
}

// Initialize the chart with the calculated number of dates
var numDays = calculateNumDates();
var startIndex = allDates.length - numDays;
updateChart(startIndex, startIndex + numDays, numDays);

// Initialize the scrollbar
var scrollbar = document.getElementById('chartScrollbar');
scrollbar.max = allDates.length - numDays; // Set the maximum value of the scrollbar

// Update the chart when the scrollbar is moved
scrollbar.addEventListener('input', function() {
  currentIndex = parseInt(this.value);
  updateChart(currentIndex, currentIndex + numDays);
});

</script>
