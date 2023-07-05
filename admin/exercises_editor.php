<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
  <meta charset="UTF-8" name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <title>ExerHub - Admin: Exercises Editor</title>
  <link rel="stylesheet" type="text/css" href="//cdn.datatables.net/1.10.25/css/jquery.dataTables.min.css">
  <script type="text/javascript" src="//code.jquery.com/jquery-3.6.0.min.js"></script>
  <script type="text/javascript" src="//cdn.datatables.net/1.10.25/js/jquery.dataTables.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"></script>
  <link rel="stylesheet" href="../style.css">
  <link rel="stylesheet" href="admin.css">
  <?php 
    require_once '../php/db.php'; 
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $exerciseName = $_POST['exercise'];
        $muscleIntensities = $_POST['muscleIntensities'];
        $exerciseId = findExerciseIdByName($exerciseName);
        foreach ($muscleIntensities as $muscleName => $intensity) {
            $muscleId = findMuscleIdByName($muscleName);
            // If intensity is zero, delete the record
            if ($intensity == 0) {
                $query = "DELETE FROM exercise_muscles WHERE exercise_id = $exerciseId AND muscle_id = $muscleId";
            } else {
                // If record exists, update it; otherwise, insert new record
                $query = "INSERT INTO exercise_muscles (exercise_id, muscle_id, intensity)
                    VALUES ($exerciseId, $muscleId, $intensity)
                    ON DUPLICATE KEY UPDATE intensity = $intensity";
            }
            query($query);
        }
    }
    $exercises = queryExercises();
    $muscles = query('SELECT * FROM muscles');
    function queryExercises() {
        $result = query('SELECT e.name AS exercise_name, e.type AS exercise_type, e.difficulty, m.name AS muscle_name, em.intensity
            FROM exercises e
            JOIN exercise_muscles em ON e.id = em.exercise_id
            JOIN muscles m ON m.id = em.muscle_id');
      
        $exercises = array();
        while ($row = mysqli_fetch_assoc($result)) {
            $exerciseName = $row['exercise_name'];
            $muscleName = $row['muscle_name'];
            $intensity = $row['intensity'];
            $exerciseType = $row['exercise_type'];
            $exerciseDifficulty = $row['difficulty'];
            
            if (!isset($exercises[$exerciseName])) {
                $exercises[$exerciseName] = array(
                    'muscles' => array(),
                    'type' => $exerciseType,
                    'difficulty' => $exerciseDifficulty
                );
            }
            $exercises[$exerciseName]['muscles'][$muscleName] = $intensity;
        }
        
        return $exercises;
    }
  ?>
</head>
<body class="dark">
  <nav>
    <div class="nav-wrapper">
      <span class="brand-logo" style="margin-left: 60px"><a href="index.html"><i class="material-icons">home</i>/</a><span class="sub-page-name">Exercises</span></span>
      <a href="index.html" data-target="side-nav" class="show-on-large sidenav-trigger"><i class="material-icons">menu</i></a>
      <ul class="right" id="top-nav"></ul>
    </div>
  </nav>
<ul class="sidenav" id="side-nav"></ul>
<main class="container" style="display: flex; flex-direction: column;">
  <div id="top-form" style="display: flex; align-items: center;">
    <label for="name" id="new-exercise-label" style="margin-right: 10px;">New Exercise: </label>
    <input type="text" id="new-exercise-name" name="new-exercise-name" placeholder="New Exercise Name" style="flex: 1; margin-right: 10px; height: 40px;">
    <select id="new-exercise-type" name="type" style="flex: 0 0 15%; margin-right: 10px; height: 40px;">
        <option value="" disabled selected>Type</option>
        <option value="Push">Push</option>
        <option value="Pull">Pull</option>
        <option value="Legs">Legs</option>
        <option value="Core">Core</option>
    </select>
    <input type="number" id="new-exercise-difficulty" name="difficulty" placeholder="Difficulty" style="flex: 0 0 10%; margin-right: 10px; height: 40px;">
  </div>
  <div style="display: flex; width: 100%;">
    <div class="left-column" style="height: 80vh; width: 50%; box-sizing: border-box; overflow-y: auto;">
      <table id="exercise-table">
        <thead>
          <tr>
            <th>Name</th>
            <th>Type:&nbsp;<select title="Filter by Type"><option value="">All Types</option></select></th>
            <th>Difficulty</th>
            <th>Muscles (Intensity)</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($exercises as $exerciseName => $exerciseData): ?>
            <tr style="background-color: #1e1e1e">
              <td><?= htmlspecialchars($exerciseName) ?></td>
              <td><?= htmlspecialchars($exerciseData['type']) ?></td>
              <td><?= htmlspecialchars($exerciseData['difficulty']) ?></td>
              <td>
                <?php foreach ($exerciseData['muscles'] as $muscleName => $intensity): ?>
                  <span><?= htmlspecialchars($muscleName) ?></span> (<?= htmlspecialchars($intensity) ?>)<br>
                <?php endforeach; ?>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <div class="right-column" style="height: 80vh; width: 50%; box-sizing: border-box; overflow-y: auto;">
      <?php foreach ($muscles as $muscle): ?>
        <div class="slider-container" style="line-height: 1">
          <label for="slider-<?= $muscle['name'] ?>" class="muscle-label"><?= $muscle['name'] ?>: <span id="slider-value-<?= $muscle['name'] ?>"></span></label>
          <input type="range" style="margin: 0 0 0 0" id="slider-<?= $muscle['name'] ?>" name="<?= $muscle['name'] ?>" min="0" max="10" value="<?= isset($muscle['intensity']) ? $muscle['intensity'] : '0' ?>">

        </div>
      <?php endforeach; ?><br>
      <button class="btn waves-effect waves-light" style="height: 40px !important; display: none;" id="update-button">Update Exercise</button>
      <button class="btn waves-effect waves-light" style="height: 40px !important;" id="add-button">Add Exercise</button>
      <button class="btn waves-effect waves-light" style="height: 40px !important; margin-left: 10px; display: none;" id="delete-button">Delete Exercise</button>
    </div>
  </div>
</main>
<script src="../js/nav.js"></script>
<script>
$(document).ready(function() {
  var exerciseTable = $('#exercise-table').DataTable({
    paging: false,
    searching: true,
    columnDefs: [
      { orderable: false, targets: [1] }
    ]
  });
  
  exerciseTable.column(1).every(function() {
    var column = this;
    var typeFilter = $(this.header()).find('select');
    typeFilter.on('change', function() {
      column.search($(this).val()).draw();
    });
    column.data().unique().sort().each(function(d) {
      typeFilter.append('<option value="' + d + '">' + d + '</option>');
    });
  });

  $('.dataTables_filter').hide();
  var exerciseData = <?php echo json_encode($exercises); ?>;
  
  // Set initial slider values
  $('.slider-container input[type="range"]').each(function() {
    var muscleName = $(this).attr('name');
    var intensity = $(this).val();
    $('#slider-value-' + muscleName).text(intensity); // Set initial value for the intensity span
    $(this).prev('.muscle-label').toggleClass('dot', intensity > 0);
  });

  // Update muscle sliders when exercise is clicked
  $('#exercise-table tbody').on('click', 'tr', function() {
    var $this = $(this);
    $this.siblings().removeClass('selected');
    $this.toggleClass('selected', !$this.hasClass('selected'));
    // if a row is selected, hide the top-form div. Otherwise, show it.
    $('#top-form').toggle(!$this.hasClass('selected'));
    // if a row is selected, selected show the update-button and hide the add-button. Otherwise, show the add-button and hide the update-button.
    $('#update-button').toggle($this.hasClass('selected'));
    $('#add-button').toggle(!$this.hasClass('selected'));
    $('#delete-button').toggle($this.hasClass('selected'));
    // if no row is selected, reset all sliders and labels to zero
    if (!$this.hasClass('selected')) {
      $('.slider-container input[type="range"]').val(0).prev('.muscle-label').removeClass('dot').find('span').text(0);
      return;
    }
    var exerciseName = $this.find('td:first-child').text();
    var muscles = exerciseData[exerciseName].muscles;
    // Reset all sliders and labels to zero
    $('.slider-container input[type="range"]').val(0).prev('.muscle-label').removeClass('dot').find('span').text(0);
    for (var muscleName in muscles) {
      if (muscles.hasOwnProperty(muscleName)) {
        var intensity = muscles[muscleName];
        $('#slider-' + muscleName).val(intensity);
        $('#slider-value-' + muscleName).text(intensity); // Set new value for the intensity span
        $('#slider-' + muscleName).prev('.muscle-label').toggleClass('dot', intensity > 0);
      }
    }
  });

  // Helper function to generate the CASE statements for the SQL query
  function generateCaseStatements(updates) {
    return updates.map(function(update) {
      return ' WHEN (SELECT id FROM muscles WHERE name = ?) THEN ' + update.intensity;
    }).join('');
  }

  // Helper function to generate the parameter array for the SQL query
    function generateParams(exerciseName, updates, isUpdate) {
    if (isUpdate) {
      var params = updates.map(function(update) {
        return update.muscle;
      });
      params.push(exerciseName);
      return params;
    } else {
      var params = [];
      updates.forEach(function(update) {
        params.push(exerciseName, update.muscle, update.intensity);
      });
      return params;
    }
  }

  // Helper function to check if at least one muscle intensity is set
  function isMuscleIntensitySet() {
    var isSet = false;
    $('.slider-container input[type="range"]').each(function() {
      if ($(this).val() > 0) {
        isSet = true;
        return false;
      }
    });
    return isSet;
  }

  function updateExerciseMuscles(exerciseName, isUpdate, successCallback) {
    var updates = [];
    $('.slider-container input[type="range"]').each(function() {
      var muscleName = $(this).attr('name');
      var intensity = $(this).val();
      if (intensity > 0) {
        updates.push({
          exercise: exerciseName,
          muscle: muscleName,
          intensity: intensity
        });
      }
    });

    if (!updates.length) {
      alert('Please set at least one muscle intensity.');
      return;
    }

    var query = isUpdate 
    ? 'UPDATE exercise_muscles SET intensity = CASE muscle_id ' + generateCaseStatements(updates) + ' ELSE intensity END WHERE exercise_id = (SELECT id FROM exercises WHERE name = ?)'
    : 'INSERT INTO exercise_muscles (exercise_id, muscle_id, intensity) SELECT (SELECT id FROM exercises WHERE name = ?), (SELECT id FROM muscles WHERE name = ?), ? FROM dual';

    $.ajax({
      url: '../php/db.php',
      type: 'POST',
      data: {
        query: query,
        params: generateParams(exerciseName, updates, isUpdate),
      },
      success: function(response) {
        if (typeof successCallback === 'function') {
          successCallback(response, updates);
        }
      },
      error: function(xhr, status, error) {
        console.error(error);
        alert('An error occurred while updating exercise muscles.');
      }
    });
  } 

  $('#update-button').click(function() {
    var exerciseName = $('#exercise-table tbody tr.selected td:first-child').text();
    if (!exerciseName || !isMuscleIntensitySet()) {
      alert('Please select an exercise.');
      return;
    }

    updateExerciseMuscles(exerciseName, true, function(response, updates) {
      var exerciseRow = $('#exercise-table tbody tr.selected');
      var newExerciseData = '';
      for (var i = 0; i < updates.length; i++) {
        var update = updates[i];
        newExerciseData += '<span>' + update.muscle + '</span> (' + update.intensity + ')<br>';
      }
      exerciseRow.find('td:last-child').html(newExerciseData);
    });
  });

  $('#add-button').click(function() {
    var exerciseName = $('#new-exercise-name').val();
    var exerciseType = $('#new-exercise-type').val();
    var exerciseDifficulty = $('#new-exercise-difficulty').val();
    if (!exerciseName || !exerciseType || !exerciseDifficulty  || !isMuscleIntensitySet()) { 
      alert('Please enter an exercise name, type and difficulty and at least one muscle intensity.');
      return;
    }

    $.ajax({
      url: '../php/db.php',
      type: 'POST',
      data: {
        query: 'INSERT INTO exercises (name, type, difficulty) VALUES (?, ?, ?)',
        params: [exerciseName, exerciseType, exerciseDifficulty],
      },
      success: function(response) {
        updateExerciseMuscles(exerciseName, false);
      },
      error: function(xhr, status, error) {
        console.error(error);
        alert('An error occurred while adding a new exercise.');
      }
    });
  });

  $('#delete-button').click(function() {
    var exerciseName = $('#exercise-table tbody tr.selected td:first-child').text();
    if (!exerciseName) {
      alert('Please select an exercise.');
      return;
    }
    // Create the AJAX request
    $.ajax({
      url: '../php/db.php',
      type: 'POST',
      data: {
        query: 'DELETE FROM exercise_muscles WHERE exercise_id = (SELECT id FROM exercises WHERE name = ?)',
        params: [exerciseName],
      },
      success: function(response) {
        // After the records are deleted, we delete the exercise record
        $.ajax({
          url: '../php/db.php',
          type: 'POST',
          data: {
            query: 'DELETE FROM exercises WHERE name = ?',
            params: [exerciseName],
          },
          success: function(response) {
            // After the exercise record is deleted, we remove the exercise from the table
            $('#exercise-table tbody tr.selected').remove();
          },
          error: function(xhr, status, error) {
            console.error(error);
            alert('An error occurred while deleting the exercise.');
          }
        });
      },
      error: function(xhr, status, error) {
        console.error(error);
        alert('An error occurred while deleting the exercise muscles.');
      }
    });
  });

  // Update muscle name labels with slider values
  $('.slider-container input[type="range"]').on('input', function() {
    var muscleName = $(this).attr('name');
    var intensity = $(this).val();
    $('#slider-value-' + muscleName).text(intensity); // Update value for the intensity span
    $(this).prev('.muscle-label').toggleClass('dot', intensity > 0);
  });
});
</script>
</body>
</html>
