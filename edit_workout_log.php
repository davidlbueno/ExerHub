<?php
$pageTitle = "Edit Workout Log";
include 'php/session.php';
require_once 'php/header.php';
require_once 'php/db_connect.php';
require_once 'php/db_query.php';
$userId = $_SESSION['user_id'];  // Make sure this line exists before using $userId

$is_new_log = isset($_GET['new_log']) && $_GET['new_log'] === 'true';

if ($is_new_log) {
  $logId = null;

  // Default start and end time to current time
  $startTime = date('Y-m-d\TH:i:s');
  $endTime = date('Y-m-d\TH:i:s');
  $duration = strtotime($endTime) - strtotime($startTime);
  $length = gmdate("H:i:s", $duration);
  $workoutId = isset($workouts[0]['id']) ? $workouts[0]['id'] : null;

  // Structured query to get the workouts selected by the current user, joined with workout names
  $workoutsQuery = "SELECT w.id, w.name FROM user_selected_workouts usw JOIN workouts w ON usw.workout_id = w.id WHERE usw.user_id = ?";
  $workoutsResult = query($conn, $workoutsQuery, [$userId]);  // Pass $userId as a parameter

  if (!$workoutsResult) {
      die("Query failed: " . mysqli_error($conn));
  }

  $workouts = [];
  while ($workoutRow = mysqli_fetch_assoc($workoutsResult)) {
      $workouts[] = ['id' => $workoutRow['id'], 'name' => $workoutRow['name']];
  }

  $workoutName = isset($workouts[0]['name']) ? $workouts[0]['name'] : "";  
  
} else {
  $logId = $_GET['log_id'];
  $userId = $_SESSION['user_id'];
  $is_admin = $_SESSION['is_admin'];
  
  // Check if the user is authorized to view this log
  $authQuery = "SELECT user_id, start_time, end_time, workout_id FROM workout_logs WHERE id = ?";
  $authResult = query($conn, $authQuery, [$logId]);
  $authRow = mysqli_fetch_assoc($authResult);

  if ($authRow['user_id'] !== $userId && !$is_admin) {
      echo "You can only view logs for your own workouts.";
      exit;
  }

  // Fetch workout id
  $workoutId = $authRow['workout_id'];

  // Fetch workout name
  $workoutQuery = "SELECT name FROM workouts WHERE id = ?";
  $workoutResult = query($conn, $workoutQuery, [$workoutId]);
  $workoutRow = mysqli_fetch_assoc($workoutResult);
  $workoutName = $workoutRow['name'];

  // Fetch and format the start time and end time
  $startTime = $authRow['start_time'];
  $endTime = $authRow['end_time'];
  $duration = strtotime($endTime) - strtotime($startTime);
  $length = gmdate("H:i:s", $duration);
}
?>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://code.jquery.com/ui/1.13.0/jquery-ui.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.14.0/Sortable.min.js"></script>

<body class="dark">
  <main class="container">
  <form id="updateLogForm">
  <?php if ($is_new_log): ?>
      <!-- Dropdown for selecting workout -->
      <select title="workout-select" id="workoutSelect">
        <?php foreach ($workouts as $workout): ?>
          <option value="<?php echo $workout['id']; ?>"><?php echo $workout['name']; ?></option>
        <?php endforeach; ?>
      </select>
    <?php else: ?>
    <h4 id="workoutNameDisplay"><?php echo $workoutName; ?></h4>
    <?php endif; ?>
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
    
    // Fetch log items
    $logItemsQuery = "SELECT * FROM workout_log_items WHERE workout_log_id = ?";
    $logItemsResult = query($conn, $logItemsQuery, [$logId]);
    
    $exerciseData = [];
    $exerciseTypeQuery = "SELECT DISTINCT type FROM exercises";
    $exerciseTypeResult = query($conn, $exerciseTypeQuery);

    while ($typeRow = mysqli_fetch_assoc($exerciseTypeResult)) {
        $type = $typeRow['type'];
        $exerciseData[$type] = [];

        // Fetch exercises by type
        $exerciseQuery = "SELECT id, name FROM exercises WHERE type = ?";
        $exerciseResult = query($conn, $exerciseQuery, [$type]);
        while ($exerciseRow = mysqli_fetch_assoc($exerciseResult)) {
            $exerciseData[$type][] = $exerciseRow;
        }
    }
    
    echo "<input type='hidden' name='log_id' value='$logId'>";
    echo "<ol style='padding-left: 28px;'>";  // Start of ordered list
    
    while ($logItemRow = mysqli_fetch_assoc($logItemsResult)) {
      $exerciseType = $logItemRow['exercise_type'];
      $exerciseId = $logItemRow['exercise_id'];
      $exerciseTime = $logItemRow['exercise_time'];
      $reps = $logItemRow['reps'];
      $warmup = $logItemRow['warmup'];
      
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
        echo "<li data-exercise-time='${exerciseTime}' class='rest' style='white-space: nowrap;'>";
        echo "<div style='display: inline-block; width: calc(100% - 80px); overflow: hidden; white-space: nowrap;'>";
        echo "<strong>Rest</strong> - ({$exerciseTime}s)";
        echo "</div>";
        echo "<div style='display: inline-block; width: 80px; z-index: 1;'>";
        echo "<i class='material-icons edit-icon'>edit</i> <i class='material-icons copy-icon'>file_copy</i> <i class='material-icons delete-icon'>delete</i>";
        echo "</div>";
        echo "</li>";
      } else {
        $warmupClass = $warmup ? 'warmup' : '';
        echo "<li class='exercise-list-item {$warmupClass}' data-exercise-id='${exerciseId}' data-exercise-time='${exerciseTime}'  data-exercise-reps='${reps}'>";
        echo "<div style='display: inline-block; width: 100%; overflow: hidden; white-space: nowrap;'>";
        echo "<strong>{$exerciseType}</strong> - {$exerciseName} (<span class='displayed-seconds'>{$exerciseTime}</span>s)";
        echo "<div style='display: inline-block; float: right; width: 80px; z-index: 1;'>";
        echo "<i class='material-icons edit-icon'>edit</i> <i class='material-icons copy-icon'>file_copy</i> <i class='material-icons delete-icon'>delete</i>";
        echo "</div>";
        echo "</div>";
        echo "<div class='exercise-details' style='top: 0px; display: block; position: relative;'>";
        echo "Actual Reps: <input type='number' id='repsInput' max='999' placeholder='Reps' style='width: 40px; height: 30px' value='${reps}'>";
        echo "Actual Seconds: <input type='number' id='secondsInput' max='999' step='5' placeholder='Seconds' style='width: 40px; height: 30px' value='${exerciseTime}'>";
        echo "</div>";
        
        echo "</li>";
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
      <input type="submit" value="Save Log" class="btn" style="margin-right: 5px !important;">
      </form>
      <a href='logs.php' class='btn'>Cancel</a>
    </div>
    <i type="button" id="close-button" class="material-icons close-btn" style="margin-bottom: 5px;">close</i>
  </main>
  <script>
    let editingItem = null;
    let selectedWorkoutId = null; // Initialize variable to hold selected workout ID

    const exerciseData = <?php echo json_encode($exerciseData); ?>;
    const logId = <?php echo isset($logId) ? json_encode($logId) : 'null'; ?>;
    const userId = <?php echo json_encode($userId); ?>;
    const workoutId = <?php echo isset($workoutId) ? json_encode($workoutId) : 'null'; ?>;
  </script>
<script src="/js/edit_workout_log.js"></script>
</body>
</html>
