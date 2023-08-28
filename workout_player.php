<?php
$pageTitle = "ExerHub - Workout Player";
include 'php/session.php';
require_once 'php/header.php';
require_once 'php/db_connect.php';
require_once 'php/db_query.php';
?>

  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
  <script src="https://sdk.amazonaws.com/js/aws-sdk-2.958.0.min.js"></script>

<body class="dark" style="padding-bottom: 0px;"> <!-- Remove this to allow room for the footer -->
  <main class="container">
    <?php
    $userId = $_GET['user_id'] ?? null;
    $workoutId = $_GET['workout_id'] ?? null;
    if ($workoutId) {
      $query = "SELECT name FROM workouts WHERE id = $workoutId";
      $result = query($conn, $query);
      $row = mysqli_fetch_assoc($result);
      $workoutName = $row['name'];
      echo "<h4>$workoutName</h4>";
      $query = "SELECT ws.type, e.id AS exercise_id, e.name AS exercise_name, ws.seconds, ws.warmup
                FROM workout_sequences ws
                LEFT JOIN exercises e ON e.id = ws.exercise_id
                WHERE ws.workout_id = $workoutId";
      $result = query($conn,$query);
      ?>
      <div class="upper-row" style="display: flex; max-width: 1000px;">
            <div class="upper-left-column" style="flex: 1; min-width: 150px;">
              <div class="controls">
                <button id="playPauseBtn" class="btn"><i class="material-icons">play_arrow</i></button>
                <button id="prevBtn" class="btn"><i class="material-icons">skip_previous</i></button>
                <button id="nextBtn" class="btn"><i class="material-icons">skip_next</i></button>
                <button id="resetBtn" class="btn"><i class="material-icons">replay</i></button>
                <div class="col s12">
                  <label for="workout-length" style="display: inline-block;">Workout Length</label>
                  <div id="workout-length" style="display: inline-block;">0:00</div>
                </div>
              </div>
            </div>
            <div class="upper-right-column" style="flex: 1;">
              <h5 class="countdown-clock">00:00:00</h5>
              <label for="countdown-clock" id="currentExerciseName"></h6>
            </div>
          </div>
      <div>
        <div class="player-content" style="display: flex; flex-direction: column;">
          <div>
          <ol class="workout-list" style="padding-left: 26px;">
            <?php
            while ($row = mysqli_fetch_assoc($result)) {
              $exerciseId = $row['exercise_id'];
              $exerciseName = $row['exercise_name'];
              $exerciseType = $row['type'];
              $seconds = $row['seconds'];
              $warmup = $row['warmup'];
              if ($exerciseType === 'Rest') {
                ?>
                <li class="rest" data-seconds="<?= $seconds ?>">
                    <strong>Rest</strong> - (<?= $seconds ?> seconds)
                    <div class="exercise-details">
                      Actual Seconds: <span class="actualSeconds">0</span>
                    </div>
                  </li>
                <?php
              } else {
                if ($warmup === 1) {
                  ?>
                  <li class="exercise-list-item warmup" data-seconds="<?= $seconds ?>" data-exercise-id="<?= $exerciseId ?>">
                    <strong><?= $exerciseType ?></strong> - <?= $exerciseName ?> (<?= $seconds ?> seconds) - Warmup
                    <div class="exercise-details">
                      Actual Reps: <input type="number" class="repsInput" max="999" placeholder="Reps" style="width: 70px; height: 30px">
                      Actual Seconds: <span class="actualSeconds">0</span>
                    </div>
                  </li>
                  <?php
                } else {
                  ?>
                  <li class="exercise-list-item" data-seconds="<?= $seconds ?>" data-exercise-id="<?= $exerciseId ?>">
                    <strong><?= $exerciseType ?></strong> - <?= $exerciseName ?> (<?= $seconds ?> seconds)
                    <div class="exercise-details">
                      Actual Reps: <input type="number" class="repsInput" max="999" placeholder="Reps" style="width: 70px; height: 30px">
                      Actual Seconds: <span class="actualSeconds">0</span>
                    </div>
                  </li>
                  <?php
                }
              }
            }
            ?>
          </ol>
            <div class="workout-complete-message">
              <h2>Workout Complete!</h2>
              <p>Congratulations on completing your workout.</p>
            </div>
          </div>
          <a href="workout.php?workout_id=<?= urlencode($workoutId) ?>&workout_name=<?= urlencode($workoutName) ?>" class="close-btn">
            <i class="material-icons">close</i>
          </a>
          <div>
            <button id="saveWorkoutBtn" class="btn" >Save Workout</button>
            <button id="viewLogBtn" class="btn" >View Log</button>
          </div>
        </div>
      </div>
      <?php
    } else {
      echo "<p>No Workout ID provided.</p>";
    }
    ?>
  </main>
<script>
  const userId = <?php echo json_encode($userId); ?>;
  const workoutId = <?php echo json_encode($workoutId); ?>;
</script>
<script src="js/workout_player.js" type="module"></script>
</body>
</html>
