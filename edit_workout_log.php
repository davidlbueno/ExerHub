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

      if ($exerciseType === "Rest") {
        echo "<li><strong>Rest</strong> - (</span><input type='number' name='exercise_time[]' class='exercise-time' value='{$exerciseTime}' min='0' step='5' style='width: 50px;'>s)</li>";
      } else {
        echo "<li><strong>{$exerciseType}</strong> - {$exerciseName} (</span><input type='number' name='exercise_time[]' class='exercise-time' value='{$exerciseTime}' min='0' step='5' style='width: 50px;'>s, <input type='number' name='exercise_reps[]' class='exercise-reps' value='{$reps}' min='0' style='width: 30px;'> reps)</li>";
      }            
    }
    echo "</ol>";
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
    const logId = <?php echo json_encode($logId); ?>;
    const userId = <?php echo json_encode($userId); ?>;
    const workoutId = <?php echo json_encode($workoutId); ?>;
  </script>
<script src="/js/edit_workout_log.js"></script>
</body>
</html>
