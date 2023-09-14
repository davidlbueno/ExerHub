<?php
$pageTitle = "Edit Workout Log";
include 'php/session.php';
require_once 'php/header.php';
require_once 'php/db_connect.php';
require_once 'php/db_query.php';

$logId = $_GET['log_id'];
$userId = $_SESSION['user_id'];
$is_admin = $_SESSION['is_admin'];

// Check if the user is authorized to view this log
$authQuery = "SELECT user_id, start_time, end_time, workout_id FROM workout_logs WHERE id = $logId";
$authResult = query($conn, $authQuery);
$authRow = mysqli_fetch_assoc($authResult);

if ($authRow['user_id'] !== $userId && !$is_admin) {
  echo "You can only view logs for your own workouts.";
  exit;
}

// Fetch workout name
$workoutId = $authRow['workout_id'];
$workoutQuery = "SELECT name FROM workouts WHERE id = $workoutId";
$workoutResult = query($conn, $workoutQuery);
$workoutRow = mysqli_fetch_assoc($workoutResult);
$workoutName = $workoutRow['name'];

// Fetch and format the start time and end time
$startTime = $authRow['start_time'];
$endTime = $authRow['end_time'];
$duration = strtotime($endTime) - strtotime($startTime);
$length = gmdate("H:i:s", $duration);
?>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>

<body class="dark">
  <main class="container">
    <h4><?php echo $workoutName; ?></h4>
    <div style="display: flex; justify-content: space-between;">
      <p>Date: <?php echo date("Y-m-d", strtotime($startTime)); ?></p>
      <p>Time: <?php echo date("H:i:s", strtotime($startTime)); ?></p>
    </div>
    <div style="display: flex; align-items: center;">
      <label for="start_time" style="margin-right: 10px;">Start Time:</label>
      <input type="datetime-local" name="start_time" id="start_time" value="<?php echo date('Y-m-d\\TH:i:s', strtotime($startTime)); ?>">
    </div>
    <?php
    include 'php/select_exercise_modal.php';
    
    $logItemsQuery = "SELECT * FROM workout_log_items WHERE workout_log_id = $logId";
    $logItemsResult = query($conn, $logItemsQuery);
    
    echo "<form id='updateLogForm' action='/php/update_log.php' method='post'>";
    echo "<input type='hidden' name='log_id' value='$logId'>";
    echo "<ol style='padding-left: 28px;'>";  // Start of ordered list
    
    while ($logItemRow = mysqli_fetch_assoc($logItemsResult)) {
      $exerciseType = $logItemRow['exercise_type'];
      $exerciseId = $logItemRow['exercise_id'];
      $exerciseTime = $logItemRow['exercise_time'];
      $reps = $logItemRow['reps'];
      
      // Fetch the exercise name based on the exerciseId
      if ($exerciseType === "Rest") {
        $exerciseName = "Rest";
      } else {
        if (isset($exerciseId) && !empty($exerciseId)) {
            $exerciseQuery = "SELECT name FROM exercises WHERE id = $exerciseId";
            $exerciseResult = query($conn, $exerciseQuery);
            $exerciseRow = mysqli_fetch_assoc($exerciseResult);
            $exerciseName = $exerciseRow['name'];
        } else {
            $exerciseName = "Unknown";
        }
      }
      
      // Create list items similar to workout.php
      if ($exerciseType === "Rest") {
        echo "<li><strong>Rest</strong> - ({$exerciseTime}s)</li>";
      } else {
        echo "<li><strong>{$exerciseType}</strong> - {$exerciseName} ({$exerciseTime}s, {$reps} reps)</li>";
      }
    }
    
    echo "</ol>";  // End of ordered list
    ?>
    <div style='display: flex; justify-content: space-between;'>
      <div style="display: flex; align-items: center; justify-content: space-between;">
        <label for="end_time" style="width: 5rem;">End Time:</label>
        <p id="end_time"><?php echo date('m/d/Y h:i:s A', strtotime($endTime)); ?></p>
      </div>
      <div style="display: flex; align-items: center;">
        <p id="duration" style='line-height: 1;'>Duration: <?php echo $length; ?></p>
      </div>
    </div>
    <div style='display: flex;'>
      <button id="openModalBtn" type="button" class="btn modal-trigger" data-target="addItemModal" style='margin-right: 5px !important;'>Add Item</button>
      <input type='submit' value='Update Log' class='btn' style='margin-right: 5px !important;'>
      <a href='logs.php' class='btn'>Cancel</a>
    </div>
  </main>
<script>
$(document).ready(function() {
  function updateEndTime() {
  const startTime = new Date($('#start_time').val());
  const durationText = $('#duration').text().split(": ")[1];
  const [hours, minutes, seconds] = durationText.split(":").map(Number);

  const durationInSeconds = hours * 3600 + minutes * 60 + seconds;
  const endTime = new Date(startTime.getTime() + durationInSeconds * 1000);

  const endTimeString = endTime.toISOString().slice(0, 19);
  $('#end_time').val(endTimeString);
}

  function updateDuration() {
  let totalExerciseTime = 0;

  // Sum up exercise_time from list items
  $("ol li").each(function() {
    const text = $(this).text();
    const timeMatch = text.match(/\((\d+)s\)/);
    if (timeMatch) {
      totalExerciseTime += parseInt(timeMatch[1], 10);
    }
  });

  const hours = Math.floor(totalExerciseTime / 3600);
  const minutes = Math.floor((totalExerciseTime % 3600) / 60);
  const seconds = totalExerciseTime % 60;

  const duration = `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
  $('#duration').text(`Duration: ${duration}`);

  // Update the end time based on the new duration
  updateEndTime();
}

  $('#start_time').change(function() {
    updateEndTime();
  });

  $(document).on('change', "input[name='exercise_time[]']", function() {
    updateDuration();
  });

  // Add this new function to handle form submission
  const updateLogButton = document.querySelector("input[type='submit']");
  const updateLogForm = document.getElementById('updateLogForm');

  updateLogButton.addEventListener('click', function(event) {
    event.preventDefault();

    // Collect exercise IDs
    const exerciseIds = Array.from(document.querySelectorAll('tr[data-exercise-id]'))
      .map(tr => tr.getAttribute('data-exercise-id'));

    // Collect other form data (exercise_type, exercise_time, reps, etc.)
    const formData = new FormData(updateLogForm);

    // Add exercise IDs to form data
    exerciseIds.forEach(id => formData.append('exercise_id[]', id));

    // Send data to server
    fetch('/php/update_log.php', {
      method: 'POST',
      body: formData
    })
    .then(response => response.json())
    .then(data => {
      // Handle server response
      console.log(data);
    })
    .catch(error => {
      // Handle errors
      console.log('Error:', error);
    });
  });
  // Initialize the modal with onCloseEnd callback
  var elems = document.querySelectorAll('.modal');
  var instances = M.Modal.init(elems, {
    onCloseEnd: function() {
      // Call the updateDuration function when the modal is closed
      updateDuration();
    }
  });
});
</script>
</body>
</html>
