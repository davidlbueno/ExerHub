// Import the helper functions
import { speak, beep, createWorkoutLogEntry, createWorkoutLogItemEntry, formatTime } from './utils.js';

document.addEventListener('DOMContentLoaded', function () {
  const nextBtn = document.getElementById('nextBtn');
  const prevBtn = document.getElementById('prevBtn');
  const resetBtn = document.getElementById('resetBtn');
  const workoutList = document.querySelector('.workout-list');
  const playPauseBtn = document.getElementById('playPauseBtn');
  const playerCloseBtn = document.querySelector('.close-btn');
  const countdownClock = document.querySelector('.countdown-clock');
  const viewLogBtn = document.getElementById('viewLogBtn');
  let workoutStartTime = null;
  let isTimerRunning = false;
  let progressPercentage = 0;
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

    const listItems = document.querySelectorAll('.workout-list li');
    listItems.forEach((li) => {
      if (!li.classList.contains('active')) {
        const exerciseDetails = li.querySelector('.exercise-details');
        if (exerciseDetails) {
          exerciseDetails.style.display = 'none';
        }
      }
    });

    const exerciseType = item.querySelector('strong').textContent.trim();
    document.getElementById('currentExerciseName').textContent = exerciseType === 'Rest' ? 'Rest' : item.innerText.split('-')[1].trim();

    const exerciseDetails = activeItem.querySelector('.exercise-details');

    if (item != firstItem && item.previousElementSibling) {
      item.classList.add('show-prev-details');
      const previousItem = item.previousElementSibling;
      const previousExerciseDetails = previousItem.querySelector('.exercise-details');
      previousItem.classList.remove('show-prev-details');
      
      previousExerciseDetails.style.display = 'block';
    }

    if (item != firstItem && item.previousElementSibling) {
      
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

  async function initialCountdown(time) {
    setButtonsDisabled(true);
    // get the exercise name from the currentExerciseName element
    const currentExerciseName = document.getElementById('currentExerciseName').textContent.split('(')[0].trim();
    let countdown = time;
    countdownClock.textContent = formatTime(countdown);
    await speak(currentExerciseName + ' in ' + time + ' seconds');
    return new Promise((resolve) => {
        const interval = setInterval(() => {
            countdown--;
            countdownClock.textContent = formatTime(countdown);
            if (countdown > 0) {
                beep(200, 520, 1, 'sine');
            } else {
                beep(200, 880, 1, 'sine');
                clearInterval(interval);
                resolve();
                setButtonsDisabled(false);
            }
        }, 1000);
    });
}

  playPauseBtn.addEventListener('click', function () {
    const activeItem = document.querySelector('.workout-list li.active');
    const firstItem = document.querySelector('.workout-list li:first-child');
    if (!activeItem) return;

    const initialDuration = parseInt(activeItem.dataset.initialDuration);

    if (isTimerRunning) {
      pauseCountdown();
      isTimerRunning = false;
      playPauseBtn.innerHTML = '<i class="material-icons">play_arrow</i>';
    } else {
      if (activeItem === firstItem && progressPercentage === 0) {
        playPauseBtn.innerHTML = '<i class="material-icons">pause</i>';
        initialCountdown(5).then(() => {
          const remainingTime = initialDuration - elapsedTime;
          startCountdown(remainingTime, progressPercentage);
          isTimerRunning = true;
        });
      } else {
        if (progressPercentage === 0) {
          const remainingTime = initialDuration - elapsedTime;
          activeItem.dataset.itemStartTime = '';
          activeItem.dataset.itemStopTime = '';
          startCountdown(remainingTime, progressPercentage);
          isTimerRunning = true;
          playPauseBtn.innerHTML = '<i class="material-icons">pause</i>';
        } else {
          const remainingTime = initialDuration - elapsedTime;
          startCountdown(remainingTime, progressPercentage);
          isTimerRunning = true;
          playPauseBtn.innerHTML = '<i class="material-icons">pause</i>';
        }
      }
    }
  });

  nextBtn.addEventListener('click', function () {
    const activeItem = document.querySelector('.workout-list li.active');
    const exerciseDetails = activeItem.querySelector('.exercise-details');
    const nextItem = activeItem.nextElementSibling;

    pauseCountdown();
    if (!activeItem.dataset.itemStopTime) {
      activeItem.dataset.itemStopTime = new Date().toISOString().slice(0, 19).replace('T', ' ');
      const actualSecondsElement = exerciseDetails.querySelector('.actualSeconds');
      const startTime = activeItem.dataset.itemStartTime;
      const stopTime = new Date().toISOString().slice(0, 19).replace('T', ' ');
      let actualSeconds;

      if (startTime) {
        actualSeconds = Math.round((new Date(stopTime) - new Date(startTime)) / 1000);
        if (actualSeconds < 1) {
          actualSeconds = 0;
        }
      } else {
        actualSeconds = 0;
      }

      actualSecondsElement.textContent = actualSeconds.toString();
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
    } else {
      const workoutCompleteMessage = document.querySelector('.workout-complete-message');
      const exerciseDetailsItems = document.querySelectorAll('.exercise-details');
      exerciseDetailsItems.forEach(function (item) {
        item.style.display = 'block';
      });
      workoutCompleteMessage.style.display = 'block';
      setButtonsDisabled(true);
      resetBtn.disabled = false;
      workoutItems.forEach(function (item) {
        item.style.marginBottom = '40px';
      });
      pauseCountdown();
      isTimerRunning = false;
      updatePlayPauseButton();
    }
  });

  prevBtn.addEventListener('click', function () {
    const activeItem = document.querySelector('.workout-list li.active');
    const prevItem = activeItem.previousElementSibling;

    if (prevItem) {
      pauseCountdown();
      activeItem.classList.remove('show-prev-details');
      const exerciseDetails = prevItem.querySelector('.exercise-details');
      exerciseDetails.style.display = 'none';
      setActiveItem(prevItem);
      const prevSeconds = parseInt(prevItem.textContent.match(/\d+/));
      resetCountdown(prevItem);
      progressPercentage = 0;
      updateCountdown(prevSeconds);
    }
  });

  resetBtn.addEventListener('click', function () {
    location.reload();
  });

  viewLogBtn.addEventListener('click', function () {
    window.location.href = 'workout_logs.php?workout_id=' + workoutId + '&user_id=' + userId;
  });

  saveWorkoutBtn.addEventListener('click', async function () {
    const lastItem = document.querySelector('.workout-list li:last-child');
    const workoutEndTime = lastItem.dataset.itemStopTime || null;
    console.log("Workout startTime: " + workoutStartTime + ", stopTime: " + workoutEndTime);
    let workoutLogId;

    try {
      const workoutLogResponse = await createWorkoutLogEntry(userId, workoutId, workoutStartTime, workoutEndTime);
      workoutLogId = workoutLogResponse;
      console.log("Workout Log ID: " + workoutLogId);

      for (const item of workoutItems) {
        const exerciseTypeElement = item.querySelector('strong');
        const exerciseType = exerciseTypeElement.textContent.trim();
        let exerciseId = null;
        let exerciseReps = 0;
        let warmup = 0;
        if (exerciseType !== 'Rest') {
          exerciseId = item.dataset.exerciseId || null;
          const repsInput = item.querySelector('.repsInput');
          exerciseReps = repsInput.value || 0;
          warmup = item.classList.contains('warmup') ? 1 : 0;
        }

        const itemStartTime = item.dataset.itemStartTime ? Date.parse(item.dataset.itemStartTime) : 0;
        const itemStopTime = item.dataset.itemStopTime ? Date.parse(item.dataset.itemStopTime) : 0;
        const exerciseTime = itemStartTime && itemStopTime ? Math.round((itemStopTime - itemStartTime) / 1000) : 0;

        await createWorkoutLogItemEntry(userId, workoutLogId, exerciseType, exerciseId, exerciseTime, exerciseReps, warmup);
      }
    } catch (error) {
      console.error("Failed to create workout log entry:", error);
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

  let requestId;

  function startCountdown(seconds, progress) {
    const activeItem = document.querySelector('.workout-list li.active');
    const initialDuration = parseInt(activeItem.textContent.match(/\d+/));
    const exerciseType = activeItem.querySelector('strong').textContent;
    activeItem.dataset.initialDuration = initialDuration;

    if (!workoutStartTime) {
      workoutStartTime = new Date().toISOString().slice(0, 19).replace('T', ' ');
    }

    if (!activeItem.dataset.itemStartTime && !elapsedTime) {
      activeItem.dataset.itemStartTime = new Date().toISOString().slice(0, 19).replace('T', ' ');
    }
    const exerciseId = activeItem.dataset.exerciseId || null;
    const repsInput = activeItem.querySelector('.repsInput');
    const reps = repsInput ? repsInput.value : null;

    startTime = performance.now() - (progress / 100) * initialDuration * 1000;
    elapsedTime = 0;

    let nextExerciseName;

    if (activeItem.nextElementSibling) {
      if (activeItem.nextElementSibling.classList.contains('exercise-list-item')) {
        let fullText = activeItem.nextElementSibling.textContent;
        nextExerciseName = fullText.split(' - ')[1].split('(')[0].trim();
        nextExerciseName = nextExerciseName + ' in 10 seconds';
      } else {
        nextExerciseName = "Rest in 10 seconds";
      }
    } else {
      nextExerciseName = "Ten seconds left";
    }

    let verbalAlerts = [
      { time: 10, message: nextExerciseName, hasSpoken: false },
      { time: 15, message: '15 seconds remaining', hasSpoken: false },
      { time: 30, message: '30 seconds remaining', hasSpoken: false },
      { time: 60, message: '1 minute remaining', hasSpoken: false },
      { time: 120, message: '2 minutes remaining', hasSpoken: false },
      { time: 180, message: '3 minutes remaining', hasSpoken: false },
      { time: 240, message: '4 minutes remaining', hasSpoken: false },
      { time: 300, message: '5 minutes remaining', hasSpoken: false },
    ];

    let previousSecondValue = -1;

    function updateCountdown() {
      const currentTime = performance.now();
      elapsedTime = (currentTime - startTime) / 1000;

      const remainingTime = initialDuration - elapsedTime;

      for (let i = 0; i < verbalAlerts.length; i++) {
        let alert = verbalAlerts[i];
        if (
          remainingTime > alert.time - 1 &&
          remainingTime < alert.time + 1 &&
          !alert.hasSpoken &&
          alert.time < initialDuration
        ) {
          speak(alert.message);
          alert.hasSpoken = true; // Once the message has been spoken, mark it as such.
        }
      }

      if (remainingTime > 0) {
        countdownClock.textContent = formatTime(remainingTime);
        progressPercentage = (1 - remainingTime / initialDuration) * 100;
        activeItem.querySelector('.progress-bar').style.width = `${progressPercentage}%`;
        if (remainingTime < 3) {
          const secondValue = Math.floor(remainingTime % 60);
          if (secondValue !== previousSecondValue) {
            previousSecondValue = secondValue; // Update the previous second value
            beep(200, 520, 1, 'sine'); // Play short beep for each second change
          }
        }
        requestId = requestAnimationFrame(updateCountdown);
      } else {
        countdownClock.textContent = formatTime(0);
        cancelAnimationFrame(requestId);
        internalCall = true;
        nextBtn.click();
        beep(200, 880, 1, 'sine'); // Play short beep at the end
        for (let alert of verbalAlerts) {
          alert.hasSpoken = false;
        }
      }
    }
        
    activeItem.classList.add('progress-bar');
    requestId = requestAnimationFrame(updateCountdown);
  }

  function pauseCountdown() {
    cancelAnimationFrame(requestId);
    isTimerRunning = false;
    updatePlayPauseButton();
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

  function setButtonsDisabled(disabled) {
    playPauseBtn.disabled = disabled;
    prevBtn.disabled = disabled;
    nextBtn.disabled = disabled;
    resetBtn.disabled = disabled;
  }
});
