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
    .modal-footer {
      background-color: #252525 !important;
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
  <main class="container">
    <?php
    $workoutId = $_GET['workout_id'] ?? null;
    if ($workoutId) {
      $query = "SELECT name FROM workouts WHERE id = $workoutId";
      $result = query($query);
      $row = mysqli_fetch_assoc($result);
      $workoutName = $row['name'];
      echo "<h1>$workoutName</h1>";

      $query = "SELECT ws.type, e.name AS exercise_name, ws.seconds
              FROM workout_sequences ws
              LEFT JOIN exercises e ON e.id = ws.exercise_id
              WHERE ws.workout_id = $workoutId";
      $result = query($query);

      echo "<ol>";
      while ($row = mysqli_fetch_assoc($result)) {
        $exerciseName = $row['exercise_name'];
        $exerciseType = $row['type'];
        $seconds = $row['seconds'];

        if ($exerciseType === 'Rest') {
          echo "<li class='rest'><strong>Rest</strong> - $seconds seconds</li>";
        } else {
          echo "<li><strong>$exerciseName</strong> - $exerciseType ($seconds seconds)</li>";
        }
      }
      echo "</ol>";

      echo '
      <button class="btn" id="startWorkoutBtn">Start Workout</button>
      <button class="btn" id="editBtn">Edit Workout</button>
      <div id="workoutModal" class="modal modal-dark" data-workout-name="' . htmlspecialchars($workoutName) . '">
        <div class="modal-content">
          <h4 id="modalTitle"></h4>
          <div class="controls">
            <h5 id="currentExerciseName"></h5>
            <button id="playPauseBtn" class="btn"><i class="material-icons">play_arrow</i></button>
            <button id="prevBtn" class="btn"><i class="material-icons">skip_previous</i></button>
            <button id="nextBtn" class="btn"><i class="material-icons">skip_next</i></button>
            <button id="resetBtn" class="btn"><i class="material-icons">replay</i></button>
            <h5 class="countdown-clock" style="display: inline-block;">00:00:00</h5>
          </div>
          <ol class="workout-list"></ol>
        </div>  
        <div class="modal-footer">
        <a href="#!" class="modal-close"><i class="material-icons">close</i></a>
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
  const modal = document.getElementById('workoutModal');
  const modalInstance = M.Modal.init(modal, { dismissible: false });
  const modalTitle = document.getElementById('modalTitle');
  const workoutName = modal.getAttribute('data-workout-name');
  modalTitle.textContent = workoutName;

  const startWorkoutBtn = document.getElementById('startWorkoutBtn');
  const editBtn = document.getElementById('editBtn');
  const workoutList = document.querySelector('.workout-list');
  const playPauseBtn = document.getElementById('playPauseBtn');
  const nextBtn = document.getElementById('nextBtn');
  const prevBtn = document.getElementById('prevBtn');
  const resetBtn = document.getElementById('resetBtn');
  const countdownClock = document.querySelector('.countdown-clock');

  function setActiveItem(item) {
    const activeItem = document.querySelector('.workout-list li.active');
    activeItem.classList.remove('active');
    item.classList.add('active');
    const exerciseName = item.querySelector('strong').textContent;
    document.getElementById('currentExerciseName').textContent = exerciseName; // Display the exercise name
  }

  const workoutItems = document.querySelectorAll('ol li');
  workoutItems.forEach(function (item, index) {
    const listItem = item.cloneNode(true);
    const progressBar = document.createElement('div');
    progressBar.classList.add('progress-bar', 'positioned');
    listItem.appendChild(progressBar);
    workoutList.appendChild(listItem);

    if (index === 0) {
      listItem.classList.add('active');
      setActiveItem(listItem);
    }

    const exerciseType = item.querySelector('strong').textContent;
    if (exerciseType === 'Rest') {
      listItem.classList.add('rest');
    }
  });

  let isTimerRunning = false;
  let progressPercentage = 0;
  let countdownInterval;
  let startTime;
  let elapsedTime;
  let internalCall = false;

  startWorkoutBtn.addEventListener('click', function () {
    modalInstance.open();
    const firstItem = document.querySelector('.workout-list li:first-child');
    const firstSeconds = parseInt(firstItem.textContent.match(/\d+/));
    updateCountdown(firstSeconds);
  });

  editBtn.addEventListener('click', function () {
    const workoutId = <?php echo json_encode($workoutId); ?>;
    const workoutName = <?php echo json_encode($workoutName); ?>;
    const editUrl = `edit_workout.php?workout_id=${workoutId}&workout_name=${encodeURIComponent(workoutName)}`;
    window.location.href = editUrl;
  });

  playPauseBtn.addEventListener('click', function () {
    const activeItem = document.querySelector('.workout-list li.active');

    if (activeItem) {
      const initialDuration = parseInt(activeItem.dataset.initialDuration);

      if (isTimerRunning) {
        pauseCountdown();
        isTimerRunning = false;
        playPauseBtn.innerHTML = '<i class="material-icons">play_arrow</i>';
      } else {
        const remainingTime = initialDuration - elapsedTime;
        startCountdown(remainingTime, progressPercentage);
        isTimerRunning = true;
        playPauseBtn.innerHTML = '<i class="material-icons">pause</i>';
      }
    }
  });

  nextBtn.addEventListener('click', function () {
    const activeItem = document.querySelector('.workout-list li.active');
    const nextItem = activeItem.nextElementSibling;

    if (nextItem) {
      pauseCountdown();
      setActiveItem(nextItem);
      const nextSeconds = parseInt(nextItem.textContent.match(/\d+/));
      resetCountdown(nextItem);
      progressPercentage = 0;
      updateCountdown(nextSeconds);
      if (internalCall) {
        internalCall = false;
        startCountdown(nextSeconds, 0);
        isTimerRunning = true;
        playPauseBtn.innerHTML = '<i class="material-icons">pause</i>';
      }
    }
  });

  prevBtn.addEventListener('click', function () {
    const activeItem = document.querySelector('.workout-list li.active');
    const prevItem = activeItem.previousElementSibling;

    if (prevItem) {
      pauseCountdown();
      setActiveItem(prevItem);
      const prevSeconds = parseInt(prevItem.textContent.match(/\d+/));
      resetCountdown(prevItem);
      progressPercentage = 0;
      updateCountdown(prevSeconds);
    }
  });

  resetBtn.addEventListener('click', function () {
    const activeItem = document.querySelector('.workout-list li.active');
    const progressBarItems = document.querySelectorAll('.workout-list li .progress-bar');

    progressBarItems.forEach(function (progressBarItem) {
      progressBarItem.style.width = '0%';
    });

    const firstItem = document.querySelector('.workout-list li:first-child');
    if (firstItem) {
      setActiveItem(firstItem);
      const firstSeconds = parseInt(firstItem.textContent.match(/\d+/));
      pauseCountdown();
      updateCountdown(firstSeconds);
      isTimerRunning = false;
      progressPercentage = 0;
      updatePlayPauseButton();
    }
  });

  function resetCountdown(item) {
    const progressBar = item.querySelector('.progress-bar');
    progressBar.style.width = '0%';

    const initialDuration = parseInt(item.textContent.match(/\d+/));
    item.dataset.initialDuration = initialDuration;
  }

  function updateCountdown(seconds) {
    countdownClock.textContent = formatTime(seconds);
  }

  function startCountdown(seconds, progress) {
    const activeItem = document.querySelector('.workout-list li.active');
    const initialDuration = parseInt(activeItem.textContent.match(/\d+/));
    activeItem.dataset.initialDuration = initialDuration;

    startTime = performance.now() - (progress / 100) * initialDuration * 1000;
    elapsedTime = 0;

    function updateCountdown() {
      const currentTime = performance.now();
      elapsedTime = (currentTime - startTime) / 1000;

      const remainingTime = initialDuration - elapsedTime;
      if (remainingTime > 0) {
        countdownClock.textContent = formatTime(remainingTime);
        progressPercentage = (1 - remainingTime / initialDuration) * 100;
        activeItem.querySelector('.progress-bar').style.width = `${progressPercentage}%`;
      } else {
        countdownClock.textContent = formatTime(0);
        clearInterval(countdownInterval);
        internalCall = true;
        nextBtn.click();
      }
    }

    activeItem.classList.add('progress-bar');

    updateCountdown();
    countdownInterval = setInterval(updateCountdown, 10);
  }

  function pauseCountdown() {
    clearInterval(countdownInterval);
    isTimerRunning = false;
    updatePlayPauseButton();
  }

  function formatTime(seconds) {
  const minutes = Math.floor(seconds / 60);
  const remainingSeconds = Math.floor(seconds % 60);
  const hundredths = Math.floor((seconds % 1) * 100);
  return `${String(minutes).padStart(2, '0')}:${String(remainingSeconds).padStart(2, '0')}:${String(hundredths).padStart(2, '0')}`;
}


  function updatePlayPauseButton() {
    playPauseBtn.innerHTML = isTimerRunning ? '<i class="material-icons">pause</i>' : '<i class="material-icons">play_arrow</i>';
  }

    // Event handler for the "modal-close" event
    modal.querySelector('.modal-close').addEventListener('click', function() {
    resetModalVars();
  });
  
  function resetModalVars() {
    // Reset variables here
    resetBtn.click();
  }
});

</script>
</body>
</html>
