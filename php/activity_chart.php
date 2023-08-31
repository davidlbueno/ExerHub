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
    height: 200px; /* Specified height */
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
var earliestDate = new Date(labels[0]);
var latestDate = new Date(labels[labels.length - 1]);
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
      return 'rgba(220, 160, 8, 0.6)';
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
    type: 'category',
    position: 'top',
    labels: datesToDisplay.map(date => new Date(date + 'T00:00:00Z').toLocaleDateString('en-US', { weekday: 'short', timeZone: 'UTC' })),
  },
  y: {
    stacked: true,
    display: true
  }
}
}});

var currentIndex = allDates.length - 14;

function updateChart(startIndex, endIndex) {
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
    myChart.update();
}

document.getElementById('prevButton').addEventListener('click', function() {
    if (currentIndex > 0) {
        currentIndex -= 1;
        updateChart(currentIndex, currentIndex + 14);
    }
});

document.getElementById('nextButton').addEventListener('click', function() {
    if (currentIndex < allDates.length - 14) {
        currentIndex += 1;
        updateChart(currentIndex, currentIndex + 14);
    }
});

function setChartHeight() {
  var windowHeight = window.innerHeight;
  var chartHeight = windowHeight * 0.8;
  document.getElementById('myChart').height = chartHeight;
}

setChartHeight();
window.addEventListener('resize', setChartHeight);
</script>
