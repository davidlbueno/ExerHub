<?php
$pageTitle = "Edit Workout";
include 'php/session.php';
require_once 'php/header.php';
require_once 'php/db_connect.php';
require_once 'php/db_query.php';
if (!isset($_SESSION['user_id'])) {
  header('Location: login.php');
  exit;
}
$userId = $_SESSION['user_id']; 
$is_admin = $_SESSION['is_admin'];
?>

<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.14.0/Sortable.min.js"></script>

<body class="dark">
<?php include 'html/nav.html'; ?>
  <main class="container">
    <div class="row">
      <div class="row">
        <div class="input-field col s12">
          <input type="text" name="workout-name" id="workout-name" placeholder="Workout Name" style="width:100%;">
        </div>
        <div class="row">
          <div class="col s12">
            <label for="workout-length" style="display: inline-block;">Workout Length</label>
          <div id="workout-length" style="display: inline-block;">0:00</div>
        </div>
      </div>
      <div class="col s12">
        <ol id="workout-list">
          <?php 
          // Get the workout ID from the URL parameter
          $workoutId = $_GET['workout_id'];

           // Fetch the workout data from the database
          $query = "SELECT * FROM workouts WHERE id = $workoutId";
          $queryResult = query($conn, $query);
          if (!$queryResult) {
            throw new Exception("Failed to fetch workout data: " . mysqli_error($conn));
          }
          $workout = mysqli_fetch_assoc($queryResult);
          // Get the is_public value for the workout
          $isPublic = $workout['is_public'];

          // Retrieve the workout sequence items from the database
          $query = "SELECT ws.type, e.name AS exercise_name, ws.seconds, ws.warmup 
                    FROM workout_sequences ws
                    LEFT JOIN exercises e ON ws.exercise_id = e.id
                    WHERE ws.workout_id = $workoutId
                    ORDER BY ws.id";
          $result = query($conn, $query);

          // Create the list items based on the retrieved data
          while ($row = mysqli_fetch_assoc($result)) {
            $exerciseType = $row['type'];
            $exerciseName = $row['exercise_name'];
            $seconds = $row['seconds'];
            $warmup = $row['warmup'];

            if ($exerciseType === "Rest") {
              echo "<li class='rest'><strong>Rest</strong> - ({$seconds}s)</li>";
            } else {
              if ($warmup === 1) {
                echo "<li class='warmup'><strong>$exerciseType</strong> - $exerciseName ({$seconds}s) - Warmup</li>";
              } else {
                echo "<li ><strong>$exerciseType</strong> - $exerciseName ({$seconds}s)</li>";
              }
            }
          }
          ?>
        </ol>
      </div>
    </div>
    <!-- modal -->
<div id="addItemModal" class="modal dark-modal">
  <div class="modal-content">
    <h5 style="margin-bottom: 5px;">Add Item</h5>
    <div>
      <div style="margin-bottom: 5px;">
        <select name="type" id="type-select">
          <option value="" disabled selected>Item</option>
          <option value="Push">Push</option>
          <option value="Pull">Pull</option>
          <option value="Legs">Legs</option>
          <option value="Core">Core</option>
          <option value="Rest">Rest</option>
        </select>
      </div>
      <div style="margin-bottom: 5px;">
        <select name="exercise" id="exercise-select" disabled>
          <option value="" disabled selected>Exercise</option>
        </select>
      </div>
      <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 5px;">
        <input type="number" name="seconds" min="0" max="300" step="5" placeholder="Seconds" style="width:48%;">
        <input type="number" name="sets" id="sets-select" min="0" max="10" step="1" placeholder="Sets" style="width:48%;">
      </div>
      <div style="margin-bottom: 5px;">
        <label>
          <input type="checkbox" name="warmup" id="warmup" style="width:100%;">
          <span>Warmup</span>
        </label>
      </div>  
      <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 5px;">      
        <button id="modal-add-item" class="btn" style="width: 48%;">Add</button>
        <button id="modal-cancel-item" class="btn modal-close" style="width: 48%;">Cancel</button>
      </div>
    </div>
    <a href="#" id="modal-closeBtn" class="close-btn" style="margin-bottom: 5px;">
      <i class="material-icons">close</i>
    </a>
  </div>
</div>
<!-- modal -->
  <div class="row">
    <div class="col s12" style="padding-bottom: 50px;">
    <button id="openModalBtn" class="btn modal-trigger" data-target="addItemModal" style="margin-bottom: 5px !important;">Add Item</button>
      <button id="clear-list-btn" class="btn" style="margin-bottom: 5px !important;">Clear List</button>
      <button id="save-workout-btn" class="btn" style="margin-bottom: 5px !important;">Update Workout</button>
      <button id="delete-workout-btn" class="btn" style="margin-bottom: 5px !important;">Delete Workout</button>
      <button id="cancel-workout-btn" class="btn" style="margin-bottom: 5px !important;">Cancel</button>
      <?php if ($is_admin == 1): ?>
            <label>
            <input type="checkbox" id="public" name="public" <?php echo $isPublic == 1 ? 'checked' : ''; ?>>
              <span style="margin-left: 10px;">Public</span>
            </label>
          <?php endif; ?>
    </div>
  </div>
</main>
<script src="js/nav.js"></script>
<script>
  document.addEventListener('DOMContentLoaded', function() {
    var script = document.createElement('script');
    script.src = 'js/create_workout.js';
    document.head.appendChild(script);
  });
</script>
<script src="js/update_workout.js"></script>
<script>
  document.addEventListener('DOMContentLoaded', function() {
    var urlParams = new URLSearchParams(window.location.search);
    var workoutName = urlParams.get('workout_name');

    if (workoutName) {
      document.getElementById('workout-name').value = workoutName;
    }
  });
</script>
<?php include 'html/footer.html'; ?>
</body>
</html>
