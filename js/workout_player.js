document.addEventListener('DOMContentLoaded', function () {
  const nextBtn = document.getElementById('nextBtn');
  const prevBtn = document.getElementById('prevBtn');
  const resetBtn = document.getElementById('resetBtn');
  const workoutList = document.querySelector('.workout-list');
  const playPauseBtn = document.getElementById('playPauseBtn');
  const playerCloseBtn = document.querySelector('.player-close-btn');
  const countdownClock = document.querySelector('.countdown-clock');
  const viewLogBtn = document.getElementById('viewLogBtn');
  let workoutStartTime = null;
  let isTimerRunning = false;
  let progressPercentage = 0;
  let countdownInterval;
  let startTime;
  let elapsedTime;
  let internalCall = false;

  const firstItem = document.querySelector('.workout-list li:first-child');
  const firstSeconds = parseInt(firstItem.textContent.match(/\d+/));
  updateCountdown(firstSeconds);

  function setActiveItem(item) {
    const activeItem = document.querySelector('.workout-list li.active');
    activeItem.classList.remove('active');
    item.classList.add('active');
    const exerciseName = item.querySelector('strong').textContent;
    document.getElementById('currentExerciseName').textContent = exerciseName;

    const exerciseDetails = activeItem.querySelector('.exercise-details');
    if (exerciseDetails) {
      exerciseDetails.style.display = 'none';
    }

    const activeExerciseDetails = item.querySelector('.exercise-details');
    if (activeExerciseDetails) {
      activeExerciseDetails.style.display = 'block';
    }

    // If the active item is a rest item, then show the previous items exercise details
    if (item.classList.contains('rest')) {
      const previousItem = item.previousElementSibling;
      const previousExerciseDetails = previousItem.querySelector('.exercise-details');
      if (previousExerciseDetails) {
        previousExerciseDetails.style.display = 'block';
      }
    }
  }

  const workoutItems = document.querySelectorAll('ol li');
  workoutItems.forEach(function (item, index) {
    const listItem = item;
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
    
    if (exerciseType === 'Warmup') {
      listItem.classList.add('warmup');
    }
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
    const exerciseType = activeItem.querySelector('strong').textContent;
    const nextItem = activeItem.nextElementSibling;

    pauseCountdown();
      if (!activeItem.dataset.itemStopTime) {
        activeItem.dataset.itemStopTime = new Date().toISOString().slice(0, 19).replace('T', ' ');
      }
      console.log("Exercise Stop Time: " + activeItem.dataset.itemStopTime);
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

  viewLogBtn.addEventListener('click', function () {
    workoutItems.forEach((item) => {
      const exerciseTypeElement = item.querySelector('strong');
      if (!exerciseTypeElement) {
        console.error("Element 'strong' not found in item");
        return;
      }
      const exerciseType = exerciseTypeElement.textContent.trim();
      let exerciseId = null;
      let exerciseReps = null;
      if (exerciseType !== 'Rest') {
        exerciseId = item.dataset.exerciseId || null; // use null if not defined
  
        // Get reps value from input field inside the current item
        const repsInput = item.querySelector('.repsInput');
        exerciseReps = repsInput ? repsInput.value : null;
      }
      const itemStartTime = item.dataset.itemStartTime || null; // use null if not defined
      const itemStopTime = item.dataset.itemStopTime || null; // use null if not defined      
      console.log(`${exerciseType}, ${exerciseId}, ${exerciseReps}, ${itemStartTime}, ${itemStopTime}`);
    });
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

  let requestId; // Variable to store the request animation frame ID

  function startCountdown(seconds, progress) {
    const activeItem = document.querySelector('.workout-list li.active');
    const initialDuration = parseInt(activeItem.textContent.match(/\d+/));
    const exerciseType = activeItem.querySelector('strong').textContent;
    const workoutName = activeItem.dataset.workoutName;
    activeItem.dataset.initialDuration = initialDuration;

    if (!workoutStartTime) {
      workoutStartTime = new Date().toISOString().slice(0, 19).replace('T', ' ');
      createWorkoutLogEntry(userId, workoutId, workoutStartTime);
    }

    if (!activeItem.dataset.itemStartTime && !elapsedTime) {
      activeItem.dataset.itemStartTime = new Date().toISOString().slice(0, 19).replace('T', ' ');
    }
    const exerciseId = activeItem.dataset.exerciseId || null;
    const repsInput = activeItem.querySelector('.repsInput');
    const reps = repsInput ? repsInput.value : null; 
    createWorkoutLogItemEntry(userId, workoutId, exerciseId, activeItem.dataset.itemStartTime, reps);

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
        requestId = requestAnimationFrame(updateCountdown); // Request the next animation frame
      } else {
        countdownClock.textContent = formatTime(0);
        cancelAnimationFrame(requestId); // Cancel the animation frame
        internalCall = true;
        nextBtn.click();
      }
    }

    activeItem.classList.add('progress-bar');
    requestId = requestAnimationFrame(updateCountdown); // Start the animation frame loop
  }

  function pauseCountdown() {
    cancelAnimationFrame(requestId); // Cancel the animation frame
    isTimerRunning = false;
    updatePlayPauseButton();
  }

  function createWorkoutLogEntry(userId, workoutId, workoutStartTime) {
    const query = "INSERT INTO workout_logs (user_id, workout_id, start_time) VALUES (?, ?, ?)";
    const params = [userId, workoutId, workoutStartTime];
    console.log("Workout Start Time: " + workoutStartTime);
    console.log(query);
    console.log(params);
    $.post('php/db.php', { query, params })
      .done(function (response) {
        const workoutLogId = response;
        console.log("Workout Log ID: " + workoutLogId);
      })
      .fail(function (error) {
        console.error("Failed to create workout log entry:", error);
      });
  }

  function createWorkoutLogItemEntry(userId, workoutId, exerciseId, itemStartTime, reps) {
    const query = "INSERT INTO workout_log_items (workout_id, exercise_id, start_time, reps) VALUES (?, ?, ?, ?)";
    const params = [workoutId, exerciseId, itemStartTime, reps];
    console.log("Exercise Start Time: " + itemStartTime);
    console.log(query);
    console.log(params);
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

  playerCloseBtn.addEventListener('click', function () {
    resetPlayerVars();
    window.location.href = 'workout_list.php?workout_id=' + userId + '&workout_name_id=' + workoutName;
  });

  function resetPlayerVars() {
    resetBtn.click();
  }
});
