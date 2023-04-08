<form method="post" action="save_workout.php">

  <!-- Workout Name -->
  <label for="workout_name">Workout Name:</label>
  <input type="text" id="workout_name" name="workout_name" required><br><br>

  <!-- Exercise Selection -->
  <label for="exercise_select">Add Exercise:</label>
  <select id="exercise_select" name="exercise_select">
    <?php
    // Connect to MySQL database
    $conn = mysqli_connect('127.0.0.1', 'bwe', 'buendavi', 'bwe');

    // Query for exercises
    $exercise_query = "SELECT * FROM exercises";
    $exercise_result = mysqli_query($conn, $exercise_query);

    // Generate select options for exercises
    while ($row = mysqli_fetch_assoc($exercise_result)) {
      echo "<option value=\"{$row['id']}\">{$row['name']}</option>";
    }

    // Close database connection
    mysqli_close($conn);
    ?>
  </select>

  <!-- Rest Period Selection -->
  <label for="rest_period_select">Rest Period:</label>
  <select id="rest_period_select" name="rest_period_select">
    <?php
    // Connect to MySQL database
    $conn = mysqli_connect('127.0.0.1', 'bwe', 'buendavi', 'bwe');

    // Query for rest periods
    $rest_period_query = "SELECT * FROM rest_periods";
    $rest_period_result = mysqli_query($conn, $rest_period_query);

    // Generate select options for rest periods
    while ($row = mysqli_fetch_assoc($rest_period_result)) {
      echo "<option value=\"{$row['id']}\">{$row['seconds']} seconds</option>";
    }

    // Close database connection
    mysqli_close($conn);
    ?>
  </select>

  <!-- Reps Input -->
  <label for="reps_input">Reps:</label>
  <input type="number" id="reps_input" name="reps_input" min="1" max="100" required><br><br>

  <!-- Add Exercise Button -->
  <button type="button" id="add_exercise_button">Add Exercise</button><br><br>

  <!-- Workout Sequence Table -->
  <table id="workout_sequence_table">
    <thead>
      <tr>
        <th>Exercise</th>
        <th>Rest Period</th>
        <th>Reps</th>
        <th></th>
      </tr>
    </thead>
    <tbody>
    </tbody>
  </table><br>

  <!-- Save Workout Button -->
  <button type="submit" id="save_workout_button">Save Workout</button>

</form>

<script>
  // Add Exercise Button Click Event
  document.getElementById('add_exercise_button').addEventListener('click', function() {

    // Get selected exercise data
    var exercise_select = document.getElementById('exercise_select');
    var exercise_id = exercise_select.value;
    var exercise_name = exercise_select.options[exercise_select.selectedIndex].text;

    // Get selected rest period data
    var rest_period_select = document.getElementById('rest_period_select');
    var rest_period_id = rest_period_select.value;
    var rest_period_seconds = rest_period_select.options[rest_period_select.selectedIndex].text.split(' ')[0];

    // Get reps input data
    var reps_input = document.getElementById('reps_input');
    var reps = reps_input.value;

    // Create new row in workout sequence
    var table = document.getElementById('workout_sequence_table').getElementsByTagName('tbody')[0];
    var row = table.insertRow(-1);
    var exercise_cell = row.insertCell(0);
    var rest_period_cell = row.insertCell(1);
    var reps_cell = row.insertCell(2);
    var delete_cell = row.insertCell(3);

    // Add data to new row
    exercise_cell.innerHTML = exercise_name;
    rest_period_cell.innerHTML = rest_period_seconds + ' seconds';
    reps_cell.innerHTML = reps;
    delete_cell.innerHTML = '<button type="button" class="delete_exercise_button">Delete</button>';

    // Delete Exercise Button Click Event
    delete_cell.getElementsByClassName('delete_exercise_button')[0].addEventListener('click', function() {
      row.remove();
    });

    // Clear form inputs
    exercise_select.selectedIndex = 0;
    rest_period_select.selectedIndex = 0;
    reps_input.value = '';

  });
</script>
