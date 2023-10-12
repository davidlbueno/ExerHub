$(document).ready(function() {
  const $startTime = $('#start_time');
  const $duration = $('#duration');
  const $endTime = $('#end_time');

  let touchStartY = 0;
  
  function formatTime(number) {
    return number.toString().padStart(2, '0');
  }

  function formatDate(date) {
    const month = formatTime(date.getMonth() + 1);
    const day = formatTime(date.getDate());
    const year = date.getFullYear();
    let hour = date.getHours();
    const minute = formatTime(date.getMinutes());
    const second = formatTime(date.getSeconds());

    let ampm = hour >= 12 ? 'PM' : 'AM';
    hour = hour % 12 || 12;

    return `${year}-${month}-${day} ${hour}:${minute}:${second} ${ampm}`;
  }

  function updateEndTime() {
    const startTime = new Date($startTime.val());
    const [hours, minutes, seconds] = $duration.text().split(": ")[1].split(":").map(Number);
    const durationInSeconds = hours * 3600 + minutes * 60 + seconds;
    const endTime = new Date(startTime.getTime() + durationInSeconds * 1000);

    $endTime.text(formatDate(endTime));
  }

  function updateDuration() {
    let totalExerciseTime = 0;
    
    // Loop through each li element to get the exercise time from data attributes
    $("ol li").each(function() {
      const exerciseTime = $(this).attr('data-exercise-time');
      totalExerciseTime += parseInt(exerciseTime, 10);
    });
    
    const hours = Math.floor(totalExerciseTime / 3600);
    const minutes = Math.floor((totalExerciseTime % 3600) / 60);
    const seconds = totalExerciseTime % 60;
  
    $duration.text(`Duration: ${formatTime(hours)}:${formatTime(minutes)}:${formatTime(seconds)}`);
    updateEndTime();
  }  
  
  $(document).on('change', ".exercise-time", updateDuration);

  $startTime.change(updateEndTime);
  $(document).on('change', "input[name='exercise_time[]']", updateDuration);

  const saveLogButton = document.querySelector("input[type='submit']");

  // import { createWorkoutLogEntry, createWorkoutLogItemEntry } from './utils.js';
  let createWorkoutLogEntry, createWorkoutLogItemEntry;
  import('./utils.js').then(module => {
    createWorkoutLogEntry = module.createWorkoutLogEntry;
    createWorkoutLogItemEntry = module.createWorkoutLogItemEntry;
  });

  saveLogButton.addEventListener('click', async function(event) {
    event.preventDefault(); // Prevent form submission
    
    const logData = {
      log_id: logId,
      user_id: userId,
      workout_id: workoutId,
      start_time: convertToDbFormat(new Date($startTime.val()).toISOString()),
      end_time: convertToDbFormat(new Date($endTime.text()).toISOString()),
      exercise_time: [],
      reps: [],
      warmup: [],
      exercise_type: [],
      exercise_id: []
    };
    
    $("ol li").each(function() {
      const $this = $(this);
      const exerciseType = $this.find("strong").text();
      logData.exercise_type.push(exerciseType);
      const isWarmup = $this.hasClass('warmup') ? 1 : 0;
      logData.warmup.push(isWarmup);
    
      if (exerciseType !== "Rest") {
        logData.exercise_id.push($this.attr("data-exercise-id"));
        logData.reps.push($this.attr('data-exercise-reps'));
      } else {
        logData.exercise_id.push(null);
        logData.reps.push(null);
      }
    
      logData.exercise_time.push($this.attr('data-exercise-time'));
    });

    if (newLog) {
      // Create a new log entry first
      try {
        const newLogData = await createWorkoutLogEntry(
          logData.user_id, 
          logData.workout_id, 
          logData.start_time, 
          logData.end_time
        );
        let jsonResponse = JSON.parse(newLogData);
        logData.log_id = jsonResponse.insert_id;
      } catch (error) {
        alert('Error creating new log');
        console.log(error);
        return;
      }
      
      try {
        // Create log items
        for (let i = 0; i < logData.exercise_type.length; i++) {
          const newItemData = await createWorkoutLogItemEntry(
            logData.user_id,
            logData.log_id,
            logData.exercise_type[i],
            logData.exercise_id[i],
            logData.exercise_time[i],
            logData.reps[i],
            logData.warmup[i]
          );
          
          console.log(newItemData);
        }
      } catch (error) {
        alert('Error creating new log item');
        console.log(error);
        return;
      }
    }

    // Update existing log and its items using php/update_log.php
    $.post('/php/update_log.php', logData, function(response) {
      if (response.success) {
        window.location.href = '/logs.php';
      } else {
        alert('Error updating log');
      }
    }, 'json');
  }); 
  
  var sortable = new Sortable(document.querySelector('ol'), {
    delay: 200,  // ms delay for touch devices
    delayOnTouchOnly: true,  // Only delay for touch devices
    // ... other options
  });
  

  $(document).on('click', '.edit-icon', function(e) {
    editingItem = $(this).closest('li');
    
    // Reset the modal fields
    $('#type-select').val('');
    $('#exercise-select').val('');
    $('input[name="seconds"]').val('');
    $('#sets-select').hide();
    $('#reps-select').val('');
    $('#warmup').prop('checked', false);
    
    // Populate the modal fields based on the clicked item
    const exerciseType = editingItem.find('strong').text();
    $('#type-select').val(exerciseType).trigger('change');
    
    const isWarmup = editingItem.hasClass('warmup');
    $('#warmup').prop('checked', isWarmup);
    
    // Fetch data attributes from the clicked li element
    const exerciseTime = editingItem.data('exercise-time');
    const reps = editingItem.data('exercise-reps');
    
    // Populate the modal fields with these values
    $('input[name="seconds"]').val(exerciseTime);
    $('#reps-select').val(reps);
    
    if (exerciseType !== 'Rest') {
      const exerciseId = editingItem.data('exercise-id');
      // Populate the exercise-select field based on exerciseType
      const exerciseSelect = document.getElementById('exercise-select');
      exerciseSelect.innerHTML = '';
      exerciseData[exerciseType].forEach(exercise => {
        const option = document.createElement('option');
        option.value = exercise.id;
        option.textContent = exercise.name;
        exerciseSelect.appendChild(option);
      });

      // Set the exercise-select field to the correct exercise
      exerciseSelect.value = exerciseId;
      $('#exercise-select').prop('disabled', false);
      $('#sets-select').prop('disabled', true);
    }
    
    // Open the modal
    var instance = M.Modal.getInstance($('#addItemModal'));
    instance.open();
  });

  $(document).on('click', '.delete-icon', function(e) {
    e.stopPropagation(); // Prevent triggering the parent li click event
    $(this).closest('li').remove(); // Remove the parent list item
    // updatetime
    updateDuration();
  });

  $(document).on('click', '.copy-icon', function(e) {
    e.stopPropagation(); // Prevent triggering the parent li click event
    const currentItem = $(this).closest('li');
    const cloneItem = currentItem.clone();
    cloneItem.insertAfter(currentItem);
    // updatetime
    updateDuration();
  });  
  
  // Convert ISO string to the database-expected format
  function convertToDbFormat(isoString) {
    const date = new Date(isoString);
    const utcDate = new Date(date.getTime() - date.getTimezoneOffset() * 60000);
    return utcDate.toISOString().replace('T', ' ').split('.')[0];
  }  

  // Close Button Event Listener
  $('#close-button').click(function() {
    window.history.back(); // Go back to the previous page
  });

  $(document).on('input', '#secondsInput', function() {
    // Update the list item's displayed seconds and data attribute
    const newItemSeconds = $(this).val();
    $(this).closest('li').attr('data-exercise-time', newItemSeconds).find('.displayed-seconds').text(newItemSeconds);
    
    // Update the workout log's duration and end time
    updateDuration();
    updateEndTime();
  });

  // update the workout ID when the workout select changes
  $(document).on('change', '#workoutSelect', function() {
    workoutId = $(this).val();
  });

  $(document).on('change', '#repsInput', function() {
    // Update the list item's displayed reps and data attribute
    const newItemReps = $(this).val();
    $(this).closest('li').attr('data-exercise-reps', newItemReps).find('.displayed-reps').text(newItemReps);
  });

  M.Modal.init(document.querySelectorAll('.modal'), { onCloseEnd: updateDuration });
});
