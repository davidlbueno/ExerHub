$(document).ready(function() {
  const $startTime = $('#start_time');
  const $duration = $('#duration');
  const $endTime = $('#end_time');
  
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
    // Data for workout_logs table
    console.log(logId);
    console.log(userId);
    console.log(workoutId);
    console.log(formatDate(new Date($startTime.val())));
    console.log($endTime.text());
    
    // Data for workout_log_items table
    $("ol li").each(function() {
      const $this = $(this);
      const exerciseType = $this.find("strong").text();
      const exerciseId = "Rest" ? "N/A" : $this.data("exercise-id");
      const exerciseTime = $this.find(".exercise-time").val();
      const exerciseReps = exerciseType === "Rest" ? "N/A" : $this.find(".exercise-reps").val();
      const isWarmup = $this.hasClass("warmup");
  
      console.log(`Type: ${exerciseType}, ID: ${exerciseId}, Time: ${exerciseTime}, Reps: ${exerciseReps}, warmup: ${isWarmup}`);
    });
  });

  M.Modal.init(document.querySelectorAll('.modal'), { onCloseEnd: updateDuration });
});
