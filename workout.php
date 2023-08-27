<?php
$pageTitle = "Workout";
include 'php/session.php';
require_once 'php/header.php';
require_once 'php/db_connect.php';
require_once 'php/db_query.php';
?>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>

<body class="dark">
  <?php include 'html/nav.html'; ?>
  <ul class="sidenav" id="side-nav"></ul>
  <main class="container">
    <?php
    $workoutId = $_GET['workout_id'] ?? null;
    if ($workoutId) {
      $query = "SELECT name, is_public FROM workouts WHERE id = $workoutId";
      $result = query($conn, $query);
      $row = mysqli_fetch_assoc($result);
      $workoutName = $row['name'];
      $isPublic = $row['is_public'];
      echo "<h4 style='display: inline-block;'>$workoutName</h4>";
      if ($isPublic) {
        echo "<span style='display: inline-block; background-color: #9fa517; color: #3b4302; font-weight: bold; padding: 0 4px 0 3px; margin: 5px; border-radius: 5px;'>Public</span>";
      }
      $query = "SELECT ws.type, e.id AS exercise_id, e.name AS exercise_name, ws.seconds, ws.warmup
                FROM workout_sequences ws
                LEFT JOIN exercises e ON e.id = ws.exercise_id
                WHERE ws.workout_id = $workoutId";
      $result = query($conn, $query);
      echo '<div class="row">
              <div class="col s12">
                <label for="workout-length" style="display: inline-block;">Workout Length</label>
                <div id="workout-length" style="display: inline-block;">0:00</div>
              </div>
            </div>';
      echo "<ol>";
      // Create the list items based on the retrieved data
      while ($row = mysqli_fetch_assoc($result)) {
        $type = $row['type'];
        $exerciseName = $row['exercise_name'];
        $seconds = $row['seconds'];
        $warmup = $row['warmup'];

        if ($type === "Rest") {
          echo "<li data-seconds={$seconds} class='rest'><strong>Rest</strong> - ({$seconds}s)</li>";
        } else {
          if ($warmup === 1) {
            echo "<li data-seconds={$seconds} class='warmup'><strong>Warmup</strong> - $exerciseName ({$seconds}s)</li>";
          } else {
            echo "<li data-seconds={$seconds} ><strong>$type</strong> - $exerciseName ({$seconds}s)</li>";
          }
        }
      }
      echo "</ol>";

      echo '
      <button class="btn" id="startWorkoutBtn">Start Workout</button>
      <button class="btn" id="editBtn">Edit Workout</button>
      <button class="btn" id="viewLogBtn">View Log</button>';
    } else {
      echo "<p>No Workout ID provided.</p>";
    }
    ?>
  </main>
  <script src="js/nav.js"></script>
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      fetchSessionVars().then(sessionVars => {
        const workoutId = <?php echo json_encode($workoutId); ?>;
        const workoutName = <?php echo json_encode($workoutName); ?>;
        const userId = sessionVars.userId;

        // Function to calculate total workout time and display it in the workout-length div
        function calculateWorkoutLength() {
          const workoutLength = document.getElementById("workout-length");
          let totalSeconds = 0;
          // get the seconds value from the dataset for each list item that has a seconds value
          const listItems = document.querySelectorAll("li");
          for (let i = 0; i < listItems.length; i++) {
            const seconds = listItems[i].dataset.seconds;
            if (seconds) {
              totalSeconds += parseInt(seconds);
            }
          }
          workoutLength.innerText = totalSeconds;
          if (totalSeconds > 0) {
            // Format the number of seconds in totalSeconds to MM:SS
            const minutes = Math.floor(totalSeconds / 60);
            const seconds = totalSeconds % 60;
            workoutLength.innerText = `${minutes}:${seconds < 10 ? '0' : ''}${seconds}`;
          }
        };

        startWorkoutBtn.addEventListener('click', function () {
          const workoutPlayerUrl = `workout_player.php?user_id=${userId}&workout_id=${workoutId}`;
          window.location.href = workoutPlayerUrl;
        });

        editBtn.addEventListener('click', function () {
          const editUrl = `edit_workout.php?workout_id=${workoutId}&workout_name=${encodeURIComponent(workoutName)}`;
          window.location.href = editUrl;
        });

        viewLogBtn.addEventListener('click', function () {
          window.location.href = 'workout_logs.php?workout_id=' + workoutId + '&user_id=' + userId;
        });
        calculateWorkoutLength();
      });
    });
  </script>
  <?php include 'html/footer.html'; ?>
</body>
</html>
