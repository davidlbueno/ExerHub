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

function createWorkoutLogItemEntry(userId, workoutLogId, exerciseType, exerciseId, exerciseTime, exerciseReps, warmup) {
  let query;
  let params;
  if (exerciseType === 'Rest') {
    query = "INSERT INTO workout_log_items (workout_log_id, exercise_type, exercise_time) VALUES (?, ?, ?)";
    params = [workoutLogId, exerciseType, exerciseTime];
  } else {
    query = "INSERT INTO workout_log_items (workout_log_id, exercise_type, exercise_id, exercise_time, reps, warmup) VALUES (?, ?, ?, ?, ?, ?)";
    params = [workoutLogId, exerciseType, exerciseId, exerciseTime, exerciseReps, warmup];
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

async function getAwsCredentials() {
  return new Promise((resolve, reject) => {
    $.ajax({
      url: '../php/get_aws_creds.php',
      type: 'GET',
      dataType: 'json',
      success: resolve,
      error: reject
    });
  });
}

async function speak(text) {
  try {
    const awsCredentials = await getAwsCredentials();
    console.log(awsCredentials);
    AWS.config.update(awsCredentials);
    const polly = new AWS.Polly();

    // Set the parameters for the SynthesizeSpeech operation
    const params = {
      OutputFormat: 'mp3',
      Text: text,
      VoiceId: 'Kendra'
    };

    // Call the SynthesizeSpeech operation
    const data = await polly.synthesizeSpeech(params).promise();
    // Create an AudioContext and play the audio data
    const audioContext = new AudioContext();
    const source = audioContext.createBufferSource();
    source.buffer = await audioContext.decodeAudioData(data.AudioStream.buffer);
    source.connect(audioContext.destination);

    return new Promise((resolve) => {
        source.onended = resolve;
        source.start(0);
    });
  } catch (error) {
    console.error('Error fetching AWS credentials:', error);
  }
}

// Export the helper functions
export { speak, beep, createWorkoutLogEntry, createWorkoutLogItemEntry, formatTime };
