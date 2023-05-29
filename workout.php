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
    $workoutId = $_GET['workout_id'] ?? null;
    if ($workoutId) {
      $query = "SELECT name FROM workouts WHERE id = $workoutId";
      $result = query($query);
      $row = mysqli_fetch_assoc($result);
      $workoutName = $row['name'];
      echo "<h1>$workoutName</h1>";

      $query = "SELECT ws.type, e.name AS exercise_name, ws.seconds, ws.sets
              FROM workout_sequences ws
              LEFT JOIN exercises e ON e.id = ws.exercise_id
              WHERE ws.workout_id = $workoutId";
      $result = query($query);

      echo "<ol>";
      while ($row = mysqli_fetch_assoc($result)) {
        $exerciseName = $row['exercise_name'];
        $exerciseType = $row['type'];
        $seconds = $row['seconds'];
        $sets = $row['sets'];

        if ($exerciseType === 'Rest') {
          echo "<li><strong>Rest</strong> - $seconds seconds</li>";
        } else {
          for ($i = 1; $i <= $sets; $i++) {
            echo "<li><strong>$exerciseName</strong> - $exerciseType ($seconds seconds)</li>";
          }
        }
      }
      echo "</ol>";

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
            <h5 class="countdown-clock" style="display: inline-block;">00:00:00</h5>
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
      let isTimerRunning = false;
      let progressPercentage = 0; // Store the progress percentage

      const modal = document.getElementById('workoutModal');
      const modalInstance = M.Modal.init(modal);
      const modalTitle = document.getElementById('modalTitle');
      const workoutName = modal.getAttribute('data-workout-name');
      modalTitle.textContent = workoutName;

      const startWorkoutBtn = document.getElementById('startWorkoutBtn');
      const workoutList = document.querySelector('.workout-list');
      const playPauseBtn = document.getElementById('playPauseBtn');
      const nextBtn = document.getElementById('nextBtn');
      const prevBtn = document.getElementById('prevBtn');
      const resetBtn = document.getElementById('resetBtn');
      const countdownClock = document.querySelector('.countdown-clock');

      const workoutItems = document.querySelectorAll('ol li');
      workoutItems.forEach(function (item, index) {
        const listItem = item.cloneNode(true);
        const progressBar = document.createElement('div');
        progressBar.classList.add('progress-bar', 'positioned');
        listItem.appendChild(progressBar);
        workoutList.appendChild(listItem);

        if (index === 0) {
          listItem.classList.add('active');
        }
      });

      startWorkoutBtn.addEventListener('click', function () {
        modalInstance.open();
        const firstItem = document.querySelector('.workout-list li:first-child');
        const firstSeconds = parseInt(firstItem.textContent.match(/\d+/));
        updateCountdown(firstSeconds);
      });

      playPauseBtn.addEventListener('click', function () {
        const activeItem = document.querySelector('.workout-list li.active');

        if (activeItem) {
          let seconds = parseInt(activeItem.textContent.match(/\d+/));

          if (isTimerRunning) {
            pauseCountdown();
            isTimerRunning = false;
            playPauseBtn.innerHTML = '<i class="material-icons">play_arrow</i>';
          } else {
            seconds = parseInt(countdownClock.textContent.split(':')[1]);
            startCountdown(seconds, progressPercentage); // Pass the stored progress percentage
            isTimerRunning = true;
            playPauseBtn.innerHTML = '<i class="material-icons">pause</i>';
          }
        }
      });

      let internalCall = false;

      nextBtn.addEventListener('click', function () {
      const activeItem = document.querySelector('.workout-list li.active');

      if (activeItem) {
        const nextItem = activeItem.nextElementSibling;

        if (nextItem) {
          activeItem.classList.remove('active');
          nextItem.classList.add('active');
          const nextSeconds = parseInt(nextItem.textContent.match(/\d+/));
          pauseCountdown();
          updateCountdown(nextSeconds);
          if (!internalCall) {
            isTimerRunning = false;
            progressPercentage = 0; // Reset the progress percentage
            updatePlayPauseButton();
          } else {
            internalCall = false;
            startCountdown(nextSeconds, 0); // Reset the progress percentage
          }
        }
      }
    });

    prevBtn.addEventListener('click', function () {
      const activeItem = document.querySelector('.workout-list li.active');

      if (activeItem) {
        const prevItem = activeItem.previousElementSibling;

        if (prevItem) {
          activeItem.classList.remove('active');
          prevItem.classList.add('active');
          const prevSeconds = parseInt(prevItem.textContent.match(/\d+/));
          pauseCountdown();
          updateCountdown(prevSeconds);
          isTimerRunning = false;
          progressPercentage = 0; // Reset the progress percentage
          updatePlayPauseButton();
        }
      }
    });

    resetBtn.addEventListener('click', function () {
      const activeItem = document.querySelector('.workout-list li.active');
      const progressBarItems = document.querySelectorAll('.workout-list li .progress-bar');

      if (activeItem) {
        const progressBar = activeItem.querySelector('.progress-bar');
        progressBar.style.width = '0%'; // Reset the width of the progress bar for the active item
        activeItem.classList.remove('active');
        activeItem.classList.remove('progress-bar');
      }

      progressBarItems.forEach(function (progressBarItem) {
        progressBarItem.style.width = '0%'; // Reset the width of all progress bars
      });

      const firstItem = document.querySelector('.workout-list li:first-child');
      if (firstItem) {
        firstItem.classList.add('active');
        const firstSeconds = parseInt(firstItem.textContent.match(/\d+/));
        pauseCountdown();
        updateCountdown(firstSeconds);
        isTimerRunning = false;
        progressPercentage = 0; // Reset the progress percentage
        updatePlayPauseButton();
      }
    });

    function formatTime(seconds) {
      const minutes = Math.floor(seconds / 60);
      const remainingSeconds = seconds % 60;
      const hundredths = Math.floor((remainingSeconds % 1) * 100);

      const formattedMinutes = String(minutes).padStart(2, '0');
      const formattedSeconds = String(Math.floor(remainingSeconds)).padStart(2, '0');
      const formattedHundredths = String(hundredths).padStart(2, '0');

      return `${formattedMinutes}:${formattedSeconds}:${formattedHundredths}`;
    }

    let countdownInterval;
    let startTime;
    let elapsedTime;

    function startCountdown(seconds, progress) {
      startTime = new Date().getTime() - progress * seconds * 10; // Calculate the start time based on progress
      elapsedTime = 0; // Initialize the elapsed time

      function updateCountdown() {
        const currentTime = new Date().getTime();
        elapsedTime = (currentTime - startTime) / 1000; // Convert milliseconds to seconds

        const remainingTime = seconds - elapsedTime;
        if (remainingTime > 0) {
          countdownClock.textContent = formatTime(remainingTime);

          // Calculate the progress percentage based on the remaining time and total seconds
          progressPercentage = (1 - remainingTime / seconds) * 100;

          // Update the width of the progress bar
          const activeItem = document.querySelector('.workout-list li.active');
          const progressBar = activeItem.querySelector('.progress-bar');
          progressBar.style.width = `${progressPercentage}%`;
        } else {
          countdownClock.textContent = formatTime(0); // Set the display to 00:00:00 when countdown is completed
          clearInterval(countdownInterval);
          internalCall = true;
          nextBtn.click();
        }
      }

      // Add the progress-bar class to the active item
      const activeItem = document.querySelector('.workout-list li.active');
      activeItem.classList.add('progress-bar');

      updateCountdown();
      countdownInterval = setInterval(updateCountdown, 10);
    }

    function pauseCountdown() {
      clearInterval(countdownInterval);
    }

    function updateCountdown(seconds) {
      countdownClock.textContent = formatTime(seconds);
    }

    function updatePlayPauseButton() {
      if (isTimerRunning) {
        playPauseBtn.innerHTML = '<i class="material-icons">pause</i>';
      } else {
        playPauseBtn.innerHTML = '<i class="material-icons">play_arrow</i>';
      }
    }
    });
  </script>
</body>
</html>
