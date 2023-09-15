$(document).ready(function() {
  const $startTime = $('#start_time');
  const $duration = $('#duration');
  const $endTime = $('#end_time');

  function formatTime(number) {
    return number.toString().padStart(2, '0');
  }

  function updateEndTime() {
    const startTime = new Date($startTime.val());
    const [hours, minutes, seconds] = $duration.text().split(": ")[1].split(":").map(Number);
    const durationInSeconds = hours * 3600 + minutes * 60 + seconds;
    const endTime = new Date(startTime.getTime() + durationInSeconds * 1000);

    const month = formatTime(endTime.getMonth() + 1);
    const day = formatTime(endTime.getDate());
    const year = endTime.getFullYear();
    let hour = endTime.getHours();
    const minute = formatTime(endTime.getMinutes());
    const second = formatTime(endTime.getSeconds());

    let ampm = hour >= 12 ? 'PM' : 'AM';
    hour = hour % 12 || 12;

    $endTime.text(`${month}/${day}/${year} ${hour}:${minute}:${second} ${ampm}`);
  }

  function updateDuration() {
    let totalExerciseTime = 0;
    $(".exercise-time").each(function() {
      totalExerciseTime += parseInt($(this).val(), 10);
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

  const updateLogButton = document.querySelector("input[type='submit']");
  const updateLogForm = document.getElementById('updateLogForm');

  updateLogButton.addEventListener('click', function(event) {
    event.preventDefault();
    const exerciseIds = Array.from(document.querySelectorAll('tr[data-exercise-id]')).map(tr => tr.getAttribute('data-exercise-id'));
    const formData = new FormData(updateLogForm);
    exerciseIds.forEach(id => formData.append('exercise_id[]', id));

    const exerciseTimes = Array.from(document.querySelectorAll("input[name='exercise_time[]']")).map(input => input.value);
    const exerciseReps = Array.from(document.querySelectorAll("input[name='exercise_reps[]']")).map(input => input.value);
    exerciseTimes.forEach(time => formData.append('exercise_time[]', time));
    exerciseReps.forEach(rep => formData.append('exercise_reps[]', rep));  

    fetch('/php/update_log.php', {
      method: 'POST',
      body: formData
    })
    .then(response => response.json())
    .then(console.log)
    .catch(console.log);
  });

  M.Modal.init(document.querySelectorAll('.modal'), { onCloseEnd: updateDuration });
});
