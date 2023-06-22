function createWorkoutLogEntry(userId, workoutId, workoutStartTime, workoutEndTime) {
  const query = "INSERT INTO workout_logs (user_id, workout_id, start_time, end_time) VALUES (?, ?, ?, ?)";
  const params = [userId, workoutId, workoutStartTime, workoutEndTime];
  console.log("Workout Start Time: " + workoutStartTime);
  console.log(query);
  console.log(params);

  return new Promise((resolve, reject) => {
    $.post('php/db.php', { query, params })
      .done(resolve)
      .fail(reject);
  });
}

function createWorkoutLogItemEntry(userId, workoutLogId, exerciseType, exerciseId, exerciseTime, exerciseReps) {
  let query;
  let params;
  if (exerciseType === 'Rest' || exerciseType === 'Warmup') {
    query = "INSERT INTO workout_log_items (workout_log_id, exercise_type, exercise_time) VALUES (?, ?, ?)";
    params = [workoutLogId, exerciseType, exerciseTime];
  } else {
    query = "INSERT INTO workout_log_items (workout_log_id, exercise_type, exercise_id, exercise_time, reps) VALUES (?, ?, ?, ?, ?)";
    params = [workoutLogId, exerciseType, exerciseId, exerciseTime, exerciseReps];
  }
  console.log(query);
  console.log(params);

  return new Promise((resolve, reject) => {
    $.post('php/db.php', { query, params })
      .done(resolve)
      .fail(reject);
  });
}

function formatTime(seconds) {
  const minutes = Math.floor(seconds / 60);
  const remainingSeconds = Math.floor(seconds % 60);
  const hundredths = Math.floor((seconds % 1) * 100);
  return `${String(minutes).padStart(2, '0')}:${String(remainingSeconds).padStart(2, '0')}:${String(hundredths).padStart(2, '0')}`;
}

// Audio helper functions

const context = new AudioContext();

function beep(duration, frequency, volume, type, callback) {
  const oscillator = context.createOscillator();
  const gainNode = context.createGain();

  oscillator.connect(gainNode);
  gainNode.connect(context.destination);

  if (volume) {
    gainNode.gain.value = volume;
  }
  if (frequency) {
    oscillator.frequency.value = frequency;
  }
  if (type) {
    oscillator.type = type;
  }
  if (callback) {
    oscillator.onended = callback;
  }

  oscillator.start(context.currentTime);
  oscillator.stop(context.currentTime + ((duration || 1) / 1000));
}

function speak(text) {
  // set speech synthesis voice to female english
  const voices = window.speechSynthesis.getVoices();
  const voice = voices.find(voice => voice.lang === 'en-US' && voice.name === 'Google US English');
  window.speechSynthesis.voice = voice;
  const utterance = new SpeechSynthesisUtterance(text);
  window.speechSynthesis.speak(utterance);
}


// Export the helper functions
export { speak, beep, createWorkoutLogEntry, createWorkoutLogItemEntry, formatTime };
