$(document).ready(function() {
  function updateEndTime() {
  const startTime = new Date($('#start_time').val());
  const durationText = $('#duration').text().split(": ")[1];
  const [hours, minutes, seconds] = durationText.split(":").map(Number);

  const durationInSeconds = hours * 3600 + minutes * 60 + seconds;
  const endTime = new Date(startTime.getTime() + durationInSeconds * 1000);

  const endTimeString = endTime.toISOString().slice(0, 19);
  $('#end_time').text(endTimeString);
}

  function updateDuration() {
  let totalExerciseTime = 0;

  // Sum up exercise_time from list items
  $("ol li").each(function() {
    const text = $(this).text();
    const timeMatch = text.match(/\((\d+)s\)/);
    if (timeMatch) {
      totalExerciseTime += parseInt(timeMatch[1], 10);
    }
  });

  const hours = Math.floor(totalExerciseTime / 3600);
  const minutes = Math.floor((totalExerciseTime % 3600) / 60);
  const seconds = totalExerciseTime % 60;

  const duration = `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
  $('#duration').text(`Duration: ${duration}`);

  // Update the end time based on the new duration
  updateEndTime();
}

$('#start_time').change(function() {
  updateEndTime();
});

  $(document).on('change', "input[name='exercise_time[]']", function() {
    updateDuration();
  });

  // Add this new function to handle form submission
  const updateLogButton = document.querySelector("input[type='submit']");
  const updateLogForm = document.getElementById('updateLogForm');

  updateLogButton.addEventListener('click', function(event) {
    event.preventDefault();

    // Collect exercise IDs
    const exerciseIds = Array.from(document.querySelectorAll('tr[data-exercise-id]'))
      .map(tr => tr.getAttribute('data-exercise-id'));

    // Collect other form data (exercise_type, exercise_time, reps, etc.)
    const formData = new FormData(updateLogForm);

    // Add exercise IDs to form data
    exerciseIds.forEach(id => formData.append('exercise_id[]', id));

    // Send data to server
    fetch('/php/update_log.php', {
      method: 'POST',
      body: formData
    })
    .then(response => response.json())
    .then(data => {
      // Handle server response
      console.log(data);
    })
    .catch(error => {
      // Handle errors
      console.log('Error:', error);
    });
  });
  // Initialize the modal with onCloseEnd callback
  var elems = document.querySelectorAll('.modal');
  var instances = M.Modal.init(elems, {
    onCloseEnd: function() {
      // Call the updateDuration function when the modal is closed
      updateDuration();
    }
  });
});
