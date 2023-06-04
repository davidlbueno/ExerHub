<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <title>BWE - Workout</title>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"></script>
  <link rel="stylesheet" href="style.css">
  <?php require_once 'php/db.php'; ?>
</head>
<body class="dark">
  <nav>
    <div class="nav-wrapper">
      <span class="brand-logo" style="margin-left: 60px"><a href="index.html">BWE/</a><a href="workouts.php">Workouts/</a><span class="sub-page-name">Workout</span></span>
      <a href="index.html" data-target="side-nav" class="show-on-large sidenav-trigger"><i class="material-icons">menu</i></a>
      <ul class="right" id="top-nav"></ul>
    </div>
  </nav>
  <ul class="sidenav" id="side-nav"></ul>
  <main class="container">
    <?php
    $workoutId = $_GET['workout_id'] ?? null;
    if ($workoutId) {
      $query = "SELECT name FROM workouts WHERE id = $workoutId";
      $result = query($query);
      $row = mysqli_fetch_assoc($result);
      $workoutName = $row['name'];
      echo "<h1>$workoutName</h1>";

      $query = "SELECT ws.type, e.id AS exercise_id, e.name AS exercise_name, ws.seconds
                FROM workout_sequences ws
                LEFT JOIN exercises e ON e.id = ws.exercise_id
                WHERE ws.workout_id = $workoutId";
      $result = query($query);

      echo "<ol>";
      while ($row = mysqli_fetch_assoc($result)) {
          $exerciseId = $row['exercise_id'];
          $exerciseName = $row['exercise_name'];
          $exerciseType = $row['type'];
          $seconds = $row['seconds'];

          if ($exerciseType === 'Rest') {
              echo "<li class='rest'><strong>Rest</strong> - $seconds seconds</li>";
          } else {
            echo "<li class='exercise-list-item' data-exercise-id='$exerciseId'>
              <strong>$exerciseName</strong> - $exerciseType ($seconds seconds)
            <div class='exercise-details'>
              <input type='number' id='repsInput' max='999' placeholder='Reps' style='width: 70px; height: 30px'>
            </div>
          </li>";
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
    startWorkoutBtn.addEventListener('click', function () {
      const workoutId = <?php echo json_encode($workoutId); ?>;
      const userId = sessionVars.userId;
      const workoutPlayerUrl = `workout_player.php?user_id=${userId}&workout_id=${workoutId}`;
      window.location.href = workoutPlayerUrl;
    });

    editBtn.addEventListener('click', function () {
      const workoutId = <?php echo json_encode($workoutId); ?>;
      const workoutName = <?php echo json_encode($workoutName); ?>;
      const editUrl = `edit_workout.php?workout_id=${workoutId}&workout_name=${encodeURIComponent(workoutName)}`;
      window.location.href = editUrl;
    });
  </script>
</body>
</html>
