<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <title>BWE - Workout</title>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"></script>
  <link rel="stylesheet" href="style.css">
  <?php require_once 'php/db.php'; ?>
  <style>
    /* CSS styles for the modal */
    .modal.modal-dark {
      background-color: #252525;
    }
  </style>
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
<main>
  <?php
  // Fetch workout name for the current workout ID
  $workoutId = $_GET['workout_id'] ?? null;
  if ($workoutId) {
    $query = "SELECT name FROM workouts WHERE id = $workoutId";
    $result = query($query);
    $row = mysqli_fetch_assoc($result);
    $workoutName = $row['name'];
    echo "<h1>$workoutName</h1>";

    // Fetch workout items for the current workout ID
    $query = "SELECT ws.type, e.name AS exercise_name, ws.seconds, ws.sets
              FROM workout_sequences ws
              LEFT JOIN exercises e ON e.id = ws.exercise_id
              WHERE ws.workout_id = $workoutId";
    $result = query($query);

    echo "<ol>";
    $itemNumber = 1;
    while ($row = mysqli_fetch_assoc($result)) {
      $exerciseName = $row['exercise_name'];
      $exerciseType = $row['type'];
      $seconds = $row['seconds'];
      $sets = $row['sets'];

      if ($exerciseType === 'Rest') {
        echo "<li><strong>Rest</strong> - $seconds seconds</li>";
      } else {
        // Handle multiple sets
        for ($i = 1; $i <= $sets; $i++) {
          echo "<li><strong>$exerciseName</strong> - $exerciseType ($seconds seconds)</li>";
        }
      }

      $itemNumber++;
    }
    echo "</ol>";

    // "Start Workout" button and modal
    echo '
    <button class="btn" id="startWorkoutBtn">Start Workout</button>
    <div id="workoutModal" class="modal modal-dark" data-workout-name="' . htmlspecialchars($workoutName) . '">
      <div class="modal-content">
        <h4 id="modalTitle"></h4>
        <div class="controls">
          <button id="playPauseBtn" class="btn"><i class="material-icons">play_arrow</i></button>
        </div>
        <ol class="workout-list"></ol>
      </div>  
      <div class="modal-footer">
        <button class="modal-close btn">Close</button>
      </div>
    </div>';
  } else {
    echo "<p>No Workout ID provided.</p>";
  }
  ?>
</main>
<script src="js/nav.js"></script>
<script>
  document.addEventListener('DOMContentLoaded', function () {
    // Initialize the modal
    const modal = document.getElementById('workoutModal');
    const modalInstance = M.Modal.init(modal);

    // Set the modal title
    const modalTitle = document.getElementById('modalTitle');
    const workoutName = modal.getAttribute('data-workout-name');
    modalTitle.textContent = workoutName;

    // Add event listener to "Start Workout" button
    const startWorkoutBtn = document.getElementById('startWorkoutBtn');
    startWorkoutBtn.addEventListener('click', function () {
      // Clear previous workout list items
      const workoutList = document.querySelector('.workout-list');
      workoutList.innerHTML = '';

      // Clone and append workout list items to the modal
      const workoutItems = document.querySelectorAll('ol li');
      workoutItems.forEach(function (item) {
        const listItem = item.cloneNode(true);
        workoutList.appendChild(listItem);
      });
    
      // Play/Pause button functionality
      const playPauseBtn = document.getElementById('playPauseBtn');
      let isPlaying = false;

      playPauseBtn.addEventListener('click', function () {
        isPlaying = !isPlaying;
        if (isPlaying) {
          playPauseBtn.innerHTML = '<i class="material-icons">pause</i>';
          // Start playing the workout
          // Replace this code with your actual workout playback logic
          console.log('Workout started.');
        } else {
          playPauseBtn.innerHTML = '<i class="material-icons">play_arrow</i>';
          // Pause the workout
          // Replace this code with your actual workout pause logic
          console.log('Workout paused.');
        }
      });

      // Open the modal
      modalInstance.open();
    });
  });
</script>
</body>
</html>
