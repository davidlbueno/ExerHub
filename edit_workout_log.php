<?php
$pageTitle = "Edit Workout Log";
include 'php/session.php';
require_once 'php/header.php';
require_once 'php/db_connect.php';
require_once 'php/db_query.php';

$logId = $_GET['log_id'];
$userId = $_SESSION['user_id'];
$is_admin = $_SESSION['is_admin'];

// Check if the user is authorized to view this log
$authQuery = "SELECT user_id, start_time, end_time, workout_id FROM workout_logs WHERE id = $logId";
$authResult = query($conn, $authQuery);
$authRow = mysqli_fetch_assoc($authResult);

if ($authRow['user_id'] !== $userId && !$is_admin) {
  echo "You can only view logs for your own workouts.";
  exit;
}

// Fetch workout name
$workoutId = $authRow['workout_id'];
$workoutQuery = "SELECT name FROM workouts WHERE id = $workoutId";
$workoutResult = query($conn, $workoutQuery);
$workoutRow = mysqli_fetch_assoc($workoutResult);
$workoutName = $workoutRow['name'];

// Fetch and format the start time and end time
$startTime = $authRow['start_time'];
$endTime = $authRow['end_time'];
$duration = strtotime($endTime) - strtotime($startTime);
$length = gmdate("H:i:s", $duration);
?>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>

<body class="dark">
  <main class="container">
    <h4><?php echo $workoutName; ?></h4>
    <div style="display: flex; justify-content: space-between;">
      <p>Date: <?php echo date("Y-m-d", strtotime($startTime)); ?></p>
      <p>Time: <?php echo date("H:i:s", strtotime($startTime)); ?></p>
    </div>
    <div style="display: flex; align-items: center;">
  <label for="start_time" style="margin-right: 10px;">Start Time:</label>
  <input type="datetime-local" name="start_time" id="start_time" value="<?php echo date('Y-m-d\\TH:i:s', strtotime($startTime)); ?>">
</div>

    <!-- modal -->
    <div id="addItemModal" class="modal dark-modal">
      <div class="modal-content">
        <h5 style="margin-bottom: 5px;">Add Item</h5>
        <div>
          <div style="margin-bottom: 5px;">
            <select name="type" id="type-select">
              <option value="" disabled selected>Item</option>
              <option value="Push">Push</option>
              <option value="Pull">Pull</option>
              <option value="Legs">Legs</option>
              <option value="Core">Core</option>
              <option value="Rest">Rest</option>
            </select>
          </div>
          <div style="margin-bottom: 5px;">
            <select name="exercise" id="exercise-select" disabled>
              <option value="" disabled selected>Exercise</option>
            </select>
          </div>
          <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 5px;">
            <input type="number" name="seconds" min="0" max="300" step="5" placeholder="Seconds" style="width:48%;">
            <input type="number" name="sets" id="sets-select" min="0" max="10" step="1" placeholder="Sets" style="width:48%;">
          </div>
          <div style="margin-bottom: 5px;">
            <label>
              <input type="checkbox" name="warmup" id="warmup" style="width:100%;">
              <span>Warmup</span>
            </label>
          </div>  
          <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 5px;">      
            <button id="modal-add-item" class="btn" style="width: 48%;">Add</button>
            <button id="modal-cancel-item" class="btn modal-close" style="width: 48%;">Cancel</button>
          </div>
        </div>
          <i id="modal-closeBtn" class="material-icons close-btn" style="margin-bottom: 5px;">close</i>
      </div>
    </div>
    <!-- modal -->
    <?php
    $logItemsQuery = "SELECT * FROM workout_log_items WHERE workout_log_id = $logId";
    $logItemsResult = query($conn, $logItemsQuery);

    echo "<form id='updateLogForm' action='/php/update_log.php' method='post'>";
    echo "<input type='hidden' name='log_id' value='$logId'>";
    echo "<table>";
    echo "<tr><th style='padding: 5px; width: 70px;'>Type</th><th style='padding: 5px;'>Exercise</th><th style='width: 25px;'>Time</th><th style='width: 25px; padding: 5px;'>Reps</th><th style='width:20px;'></th></tr>";

while ($logItemRow = mysqli_fetch_assoc($logItemsResult)) {
  $exerciseType = $logItemRow['exercise_type'];
  $exerciseId = $logItemRow['exercise_id'];
  $exerciseTime = $logItemRow['exercise_time'];
  $reps = $logItemRow['reps'];

  // Fetch the exercise name based on the exerciseId
  if ($exerciseType === "Rest") {
    $exerciseName = "Rest";
  } else {
    if (isset($exerciseId) && !empty($exerciseId)) {
        $exerciseQuery = "SELECT name FROM exercises WHERE id = $exerciseId";
        $exerciseResult = query($conn, $exerciseQuery);
        $exerciseRow = mysqli_fetch_assoc($exerciseResult);
        $exerciseName = $exerciseRow['name'];
    } else {
        $exerciseName = "Unknown";
    }
  }

  // Determine the background color based on exercise type
  $bgColor = "";
  $dataExerciseId = "";
  if ($exerciseType === "Rest") {
    $bgColor = "style='background-color: darkgreen;'";
  } elseif ($exerciseType === "Warmup") {
    $bgColor = "style='background-color: darkblue;'";
  } else {
    $dataExerciseId = "data-exercise-id='$exerciseId'";
  }

  echo "<tr $bgColor $dataExerciseId>";
  echo "<td style='padding: 0 5px;'>";
  echo "<select name='exercise_type[]' class='type-select'>";
  echo "<option value='Push' " . ($exerciseType === 'Push' ? 'selected' : '') . ">Push</option>";
  echo "<option value='Pull' " . ($exerciseType === 'Pull' ? 'selected' : '') . ">Pull</option>";
  echo "<option value='Legs' " . ($exerciseType === 'Legs' ? 'selected' : '') . ">Legs</option>";
  echo "<option value='Core' " . ($exerciseType === 'Core' ? 'selected' : '') . ">Core</option>";
  echo "<option value='Rest' " . ($exerciseType === 'Rest' ? 'selected' : '') . ">Rest</option>";
  echo "</select>";
  echo "</td>";
  echo "<td style='padding: 0 5px;'>";
  echo "<select name='exercise_name[]' class='exercise-select'>";
  echo "<option value='$exerciseName' selected>$exerciseName</option>";
  echo "</select>";
  echo "</td>";
  echo "<td style='padding: 0 5px;'><input type='number' name='exercise_time[]' value='$exerciseTime' min='0' step='5'></td>";
  echo "<td style='padding: 0 5px;'><input class='reps-input' type='number' name='reps[]' value='$reps' min='0' step='1'></td>";
  echo "<td style='padding: 0 5px;'><a href='#' class='delete-btn' data-log-id='$logId'><i class='material-icons'>delete</i></a></td>";
  echo "</tr>";
}
echo "</table><br>";
?>
    <div style='display: flex; justify-content: space-between;'>
      <div style="display: flex; align-items: center; justify-content: space-between;">
        <label for="end_time" style="width: 5rem;">End Time:</label>
        <p id="end_time"><?php echo date('m/d/Y h:i:s A', strtotime($endTime)); ?></p>
      </div>
      <div style="display: flex; align-items: center;">
        <p id="duration" style='line-height: 1;'>Duration: <?php echo $length; ?></p>
      </div>
    </div>
    <div style='display: flex;'>
      <button id="openModalBtn" type="button" class="btn modal-trigger" data-target="addItemModal" style='margin-right: 5px !important;'>Add Item</button>
      <input type='submit' value='Update Log' class='btn' style='margin-right: 5px !important;'>
      <a href='logs.php' class='btn'>Cancel</a>
    </div>
  
    <a href="logs.php" id="closeBtn" class="close-btn">
      <i class="material-icons">close</i>
    </a>
  </main>
  <script>
  const typeSelect = document.getElementById("type-select");
  const exerciseSelect = document.getElementById("exercise-select");
  const setsSelect = document.getElementById("sets-select");

  $(document).ready(function() {
    function updateEndTime() {
      const startTime = new Date($('#start_time').val());
      let totalExerciseTime = 0;

      $("input[name='exercise_time[]']").each(function() {
        totalExerciseTime += parseInt($(this).val(), 10) || 0;
      });

      const endTime = new Date(startTime.getTime() + totalExerciseTime * 1000);
      function formatLocalDate(date) {
        const options = { year: 'numeric', month: '2-digit', day: '2-digit', hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: true };
        return new Intl.DateTimeFormat('en-US', options).format(date).replace(", ", " ");
      }
      const formattedEndTime = formatLocalDate(endTime);
      $('#end_time').text(formattedEndTime);
    }

    function updateDuration() {
      let totalExerciseTime = 0;

      $("input[name='exercise_time[]']").each(function() {
        totalExerciseTime += parseInt($(this).val(), 10) || 0;
      });

      const duration = totalExerciseTime;
      const hours = String(Math.floor(duration / 3600) % 24).padStart(2, '0');
      const minutes = String(Math.floor(duration / 60) % 60).padStart(2, '0');
      const seconds = String(duration % 60).padStart(2, '0');
      $('#duration').text(`Duration: ${hours}:${minutes}:${seconds}`);
      updateEndTime();
    }

    $('#start_time').change(function() {
      updateEndTime();
    });

    $(document).on('change', "input[name='exercise_time[]']", function() {
      updateDuration();
    });

    // Function to disable reps input for 'Rest' type
    function disableRepsForRest() {
      $('.type-select').each(function() {
        const type = $(this).val();
        const repsInput = $(this).closest('tr').find('.reps-input')[0];
        if (type === 'Rest') {
          repsInput.disabled = true;
        } else {
          repsInput.disabled = false;
        }
      });
    }

    // Call the function initially to set the correct state
    disableRepsForRest();

    // Event listener for typeSelect change
    typeSelect.addEventListener("change", () => {
      updateExerciseSelect(typeSelect.value);
    });

  // Function to update exercise select options
  async function updateExerciseSelect(selectedType, callback) {
    const response = await fetch(`php/get_exercises.php?type=${selectedType}`);
    const exercises = await response.json();

    exerciseSelect.innerHTML = 
    `<option value="" disabled selected>Exercise</option>
      ${exercises.map(exercise => `<option value="${exercise.name}">${exercise.name}</option>`).join('')}`;
      // add exercise id as data attribute
      exercises.forEach(exercise => {
        const option = $(`#exercise-select option[value='${exercise.name}']`);
        option.data('id', exercise.id);
      });
    exerciseSelect.disabled = selectedType === 'Rest';

    if (callback) {
      callback();
    }
  }

  //Initialize the modal
  var elems = document.querySelectorAll('.modal');
  var instances = M.Modal.init(elems, {
    onOpenEnd: function() {
      typeSelect.focus();
    }
  });

  // Add event listener for the close button
  document.getElementById("modal-closeBtn").addEventListener("click", function() {
    var instance = M.Modal.getInstance(document.getElementById("addItemModal"));
    instance.close();
  });

  // Add event listener for the "Add" button in the modal
  $('#modal-add-item').click(function() {
    const type = $('#type-select').val();
    const exerciseOption = type === "Rest" ? null : $('#exercise-select option:selected');
    const exercise = exerciseOption ? exerciseOption.text() : "";
    const exerciseId = exerciseOption ? exerciseOption.data('id') : null;
    const seconds = $('input[name="seconds"]').val();
    const sets = parseInt($('#sets-select').val(), 10);
    const isWarmup = $('#warmup').is(':checked');

    let bgColor = "";
    let disabled = "";
    if (isWarmup) {
      bgColor = "style='background-color: darkblue;'";
    } else if (type === "Rest") {
      bgColor = "style='background-color: darkgreen;'";
      disabled = "disabled";
    }

    let dataExerciseId = "";
    if (exerciseId) {
      dataExerciseId = `data-exercise-id='${exerciseId}'`;
    }

    for (let i = 0; i < sets; i++) {
      let newRow = `<tr ${bgColor} ${dataExerciseId}>
        <td><input type='text' name='exercise_type[]' value='${type}'></td>
        <td><input type='text' name='exercise_name[]' value='${exercise}' ${disabled}></td>
        <td><input type='number' name='exercise_time[]' value='${seconds}' min='0' step='5'></td>
        <td><input type='number' name='reps[]' value='' min='0' step='1'></td>
        <td style='padding: 0 5px;'><a href='#' class='delete-btn' data-log-id='$logId'><i class='material-icons'>delete</i></a></td>
      </tr>`;

      $('table').append(newRow);
    }

    var instance = M.Modal.getInstance($('#addItemModal'));
    instance.close();
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

  
});
</script>
</body>
</html>
