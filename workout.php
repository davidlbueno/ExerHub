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
        <button id="prevBtn" class="btn"><i class="material-icons">skip_previous</i></button>
        <button id="nextBtn" class="btn"><i class="material-icons">skip_next</i></button>
        <button id="resetBtn" class="btn"><i class="material-icons">replay</i></button>
        <div class="countdown-clock" style="display: inline-block;">00:00</div>
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
    let isTimerRunning = false; // Variable to track the timer state

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
      const workoutItemsArray = Array.from(workoutItems); // Convert NodeList to array

      workoutItemsArray.forEach(function (item, index) {
        const listItem = item.cloneNode(true);
        workoutList.appendChild(listItem);

        // Add the 'active' class to the first list item
        if (index === 0) {
          listItem.classList.add('active');
        }
      });

      // Open the modal
      modalInstance.open();
    });

    // Add event listener to "Play/Pause" button inside the modal
    const playPauseBtn = document.getElementById('playPauseBtn');
    playPauseBtn.addEventListener('click', function () {
      const activeItem = document.querySelector('.workout-list li.active');

      if (activeItem) {
        const seconds = parseInt(activeItem.textContent.match(/\d+/));

        if (isTimerRunning) {
          // Pause the timer
          pauseCountdown();
          isTimerRunning = false;
          playPauseBtn.innerHTML = '<i class="material-icons">play_arrow</i>'; // Set button appearance to "Play"
        } else {
          // Start the timer
          startCountdown(seconds);
          isTimerRunning = true;
          playPauseBtn.innerHTML = '<i class="material-icons">pause</i>'; // Set button appearance to "Pause"
        }
      }
    });

    // Add event listener to "Next" button
    const nextBtn = document.getElementById('nextBtn');
    nextBtn.addEventListener('click', function () {
      const activeItem = document.querySelector('.workout-list li.active');

      if (activeItem) {
        const nextItem = activeItem.nextElementSibling;

        if (nextItem) {
          activeItem.classList.remove('active');
          nextItem.classList.add('active');
          const nextSeconds = parseInt(nextItem.textContent.match(/\d+/));
        }
      }
    });

    // Add event listener to "Previous" button
    const prevBtn = document.getElementById('prevBtn');
    prevBtn.addEventListener('click', function () {
      const activeItem = document.querySelector('.workout-list li.active');

      if (activeItem) {
        const prevItem = activeItem.previousElementSibling;

        if (prevItem) {
          activeItem.classList.remove('active');
          prevItem.classList.add('active');
          const prevSeconds = parseInt(prevItem.textContent.match(/\d+/));
        }
      }
    });

    // Add event listener to "Reset" button
    const resetBtn = document.getElementById('resetBtn');
    resetBtn.addEventListener('click', function () {
      const activeItem = document.querySelector('.workout-list li.active');
      const firstItem = document.querySelector('.workout-list li:first-child');

      if (activeItem) {
        activeItem.classList.remove('active');
      }

      if (firstItem) {
        firstItem.classList.add('active');
        const firstSeconds = parseInt(firstItem.textContent.match(/\d+/));
        startCountdown(firstSeconds);
      }
    });

    // Format time in mm:ss:ms format (minutes:seconds:hundredths)
    function formatTime(seconds) {
      const minutes = Math.floor(seconds / 60);
      const remainingSeconds = seconds % 60;
      const hundredths = Math.floor((remainingSeconds % 1) * 100);

      const formattedMinutes = String(minutes).padStart(2, '0');
      const formattedSeconds = String(Math.floor(remainingSeconds)).padStart(2, '0');
      const formattedHundredths = String(hundredths).padStart(2, '0');

      return `${formattedMinutes}:${formattedSeconds}:${formattedHundredths}`;
    }

    // Countdown timer function
    let countdownInterval;

    function startCountdown(seconds) {
      const countdownClock = document.querySelector('.countdown-clock');

      function updateCountdown() {
        countdownClock.textContent = formatTime(seconds);

        if (seconds > 0) {
          seconds -= 0.01;
        } else {
          clearInterval(countdownInterval);
          nextBtn.click(); // Call the nextBtn.click() function
        }
      }

      updateCountdown();
      countdownInterval = setInterval(updateCountdown, 10); // Update every hundredth of a second (10ms)
    }

    // Pause the countdown timer
    function pauseCountdown() {
      clearInterval(countdownInterval);
    }
  });
</script>
</body>
</html>
