<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <title>BWE - Workout Player</title>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"></script>
  <link rel="stylesheet" href="style.css">
  <?php require_once 'php/db.php'; ?>
</head>
<body class="dark">
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
    }
    ?>
    <div class="container" style="display: flex; flex-direction: column;">
      <div class="upper-row" style="display: flex;">
        <div class="upper-left-column" style="flex: 1;">
          <h4 id="modalTitle"></h4>
          <div class="controls">
            <button id="playPauseBtn" class="btn"><i class="material-icons">play_arrow</i></button>
            <button id="prevBtn" class="btn"><i class="material-icons">skip_previous</i></button>
            <button id="nextBtn" class="btn"><i class="material-icons">skip_next</i></button>
            <button id="resetBtn" class="btn"><i class="material-icons">replay</i></button>
          </div>
        </div>
        <div class="upper-right-column" style="flex: 1;">
          <h6 id="currentExerciseName"></h6>
          <h5 class="countdown-clock">00:00:00</h5>
        </div>
      </div>
      <div>
        <ol class="workout-list"></ol>
      </div>
      <a href="#!" class="modal-close"><i class="material-icons">close</i></a>
      <div class="modal-footer" style="background-color: #252525">
        <button class="btn" id="view_log">View Log</button>
    </div>
  </main>
  <script>
  document.addEventListener('DOMContentLoaded', function () {
  const workoutName = <?php echo json_encode($workoutName); ?>;
  document.title = workoutName;
  
  const workoutList = document.querySelector('.workout-list');
  const playPauseBtn = document.getElementById('playPauseBtn');
  const nextBtn = document.getElementById('nextBtn');
  const prevBtn = document.getElementById('prevBtn');
  const resetBtn = document.getElementById('resetBtn');
  const countdownClock = document.querySelector('.countdown-clock');
  let workoutStartTime = null;

  const firstItem = document.querySelector('.workout-list li:first-child');
  const firstSeconds = parseInt(firstItem.textContent.match(/\d+/));
  updateCountdown(firstSeconds);

  function setActiveItem(item) {
    const activeItem = document.querySelector('.workout-list li.active');
    activeItem.classList.remove('active');
    item.classList.add('active');
    const exerciseName = item.querySelector('strong').textContent;
    document.getElementById('currentExerciseName').textContent = exerciseName; // Display the exercise name

    const exerciseDetails = activeItem.querySelector('.exercise-details');
    if (exerciseDetails) {
      exerciseDetails.style.display = 'none'; // Hide previously active exercise details
    }

    const activeExerciseDetails = item.querySelector('.exercise-details');
    if (activeExerciseDetails) {
      activeExerciseDetails.style.display = 'block'; // Show the exercise details for the active item
    }
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
    const exerciseType = activeItem.querySelector('strong').textContent;
    const nextItem = activeItem.nextElementSibling;

      pauseCountdown();
      if (exerciseType != 'Rest') {
        if (!activeItem.dataset.exerciseStopTime) {
          activeItem.dataset.exerciseStopTime = new Date().toISOString().slice(0, 19).replace('T', ' ');
        }
        console.log("Exercise Stop Time: " + activeItem.dataset.exerciseStopTime);
      }
      if (nextItem) {
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
    elapsedTime = 0;

    const initialDuration = parseInt(item.textContent.match(/\d+/));
    item.dataset.initialDuration = initialDuration;
  }

  function updateCountdown(seconds) {
    countdownClock.textContent = formatTime(seconds);
  }

  function startCountdown(seconds, progress) {
    const activeItem = document.querySelector('.workout-list li.active');
    const initialDuration = parseInt(activeItem.textContent.match(/\d+/));
    const exerciseType = activeItem.querySelector('strong').textContent;
    activeItem.dataset.initialDuration = initialDuration;

    // Create Workout Log Entry
    const userId = sessionVars.userId;
    const workoutId = <?php echo json_encode($workoutId); ?>;
    if (!workoutStartTime) {
      workoutStartTime = new Date().toISOString().slice(0, 19).replace('T', ' ');
      createWorkoutLogEntry(userId, workoutId, workoutStartTime);
    }

    // Add Exercise details
    if (exerciseType != 'Rest') {
      const exerciseId = activeItem.dataset.exerciseId;
      const reps = activeItem.querySelector('#repsInput').value;
      if (!activeItem.dataset.exerciseStartTime && !elapsedTime) {
        activeItem.dataset.exerciseStartTime = new Date().toISOString().slice(0, 19).replace('T', ' ');
      }
      createWorkoutLogItemEntry();
    }

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

  function createWorkoutLogItemEntry() {
    const activeItem = document.querySelector('.workout-list li.active');
    const workoutId = <?php echo json_encode($workoutId); ?>;
    const exerciseStartTime = activeItem.dataset.exerciseStartTime;
    const exerciseId = activeItem.dataset.exerciseId;
    const query = "INSERT INTO workout_log_items (workout_id, exercise_id, start_time) VALUES (?, ?, ?)";
    const params = [workoutId, exerciseId, exerciseStartTime];
      console.log("Exercise Start Time: " + exerciseStartTime);
      console.log(query);
      console.log(params);
  }

  function createWorkoutLogEntry(userId, workoutId, workoutStartTime) {
    const query = "INSERT INTO workout_logs (user_id, workout_id, start_time) VALUES (?, ?, ?)";
    const params = [userId, workoutId, workoutStartTime];
    console.log("Workout Start Time: " + workoutStartTime);
    console.log(query);
    console.log(params);
    $.post('php/db.php', { query, params })
      .done(function(response) {      
        const workoutLogId = response;
        console.log("Workout Log ID: " + workoutLogId);
      })
      .fail(function(error) {
        console.error("Failed to create workout log entry:", error);
      });
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
