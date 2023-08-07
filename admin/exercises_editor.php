<?php
  include '../php/header.php'; 
  require_once '../php/db_connect.php';
  require_once '../php/db_query.php'; 
?>
  <meta charset="UTF-8" name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <title>ExerHub - Admin: Exercises Editor</title>
  <link rel="stylesheet" type="text/css" href="//cdn.datatables.net/1.10.25/css/jquery.dataTables.min.css">
  <script type="text/javascript" src="//code.jquery.com/jquery-3.6.0.min.js"></script>
  <script type="text/javascript" src="//cdn.datatables.net/1.10.25/js/jquery.dataTables.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"></script>
  <link rel="stylesheet" href="../css/style.css">
  <link rel="stylesheet" href="css/admin.css">
  <?php
    $exercises = queryExercises($conn);
    $muscles = query($conn, 'SELECT * FROM muscles');
    function queryExercises($conn) {
        $result = query($conn, 'SELECT e.id AS exercise_id, e.name AS exercise_name, e.type AS exercise_type, e.difficulty, m.name AS muscle_name, em.intensity
          FROM exercises e
          JOIN exercise_muscles em ON e.id = em.exercise_id
          JOIN muscles m ON m.id = em.muscle_id');
        $exercises = array();
        while ($row = mysqli_fetch_assoc($result)) {
            $exerciseId = $row['exercise_id'];
            $exerciseName = $row['exercise_name'];
            $muscleName = $row['muscle_name'];
            $intensity = $row['intensity'];
            $exerciseType = $row['exercise_type'];
            $exerciseDifficulty = $row['difficulty'];
            
            if (!isset($exercises[$exerciseName])) {
                $exercises[$exerciseName] = array(
                    'exercise_id' => $exerciseId,
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
      <span class="brand-logo" style="margin-left: 60px"><a href="index.html"><i class="material-icons">home</i></a><a href="/admin/index.html">/Admin/</a><span class="sub-page-name">Exercises Editor</span></span>
      <a href="index.html" data-target="side-nav" class="show-on-large sidenav-trigger"><i class="material-icons">menu</i></a>
      <ul class="right" id="top-nav"></ul>
    </div>
  </nav>
<ul class="sidenav" id="side-nav"></ul>
<main class="container" style="display: flex; flex-direction: column;">
  <div id="top-form" style="display: flex; align-items: center;">
    <label for="name" id="exercise-label" style="margin-right: 10px;">New Exercise: </label>
    <input type="text" id="exercise-name" name="exercise-name" placeholder="New Exercise Name" style="flex: 1; margin-right: 10px; height: 40px;">
    <select id="exercise-type" name="type" style="flex: 0 0 15%; margin-right: 10px; height: 40px;">
        <option value="" disabled selected>Type</option>
        <option value="Push">Push</option>
        <option value="Pull">Pull</option>
        <option value="Legs">Legs</option>
        <option value="Core">Core</option>
    </select>
    <input type="number" id="exercise-difficulty" name="difficulty" placeholder="Difficulty" style="flex: 0 0 10%; margin-right: 10px; height: 40px;">
  </div>
  <div style="margin: 2px 0 5px 5px;">
    <textarea id="description" name="description" placeholder="Exercise Description..." rows="4" cols="50"></textarea>
  </div>
  <div style="display: flex; width: 100%;">
    <div class="left-column" style="height: 80vh; width: 50%; box-sizing: border-box; overflow-y: auto;">
      <table id="exercise-table">
        <thead>
          <tr>
            <th>Name</th>
            <th><select title="Filter by Type"><option value="">All Types</option></select></th>
            <th>Difficulty</th>
            <th>Muscles</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($exercises as $exerciseName => $exerciseData): ?>
            <tr data-exercise-id="<?= htmlspecialchars($exerciseData['exercise_id']) ?>">
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
// Helper function to check if at least one muscle intensity is set
function isMuscleIntensitySet() {
  return $('.slider-container input[type="range"]').toArray().some(function(input) {
    return $(input).val() > 0;
  });
}

$(document).ready(function() {
  var muscleIds = {};
  var selectedExerciseId; 

  var exerciseTable = $('#exercise-table').DataTable({
    paging: false,
    searching: true,
    columnDefs: [{ orderable: false, targets: [1] }]
  });

  $.getJSON('php/get_muscle_ids.php', function(data) {
    muscleIds = data;
  });

  exerciseTable.column(1).every(function() {
    var column = this;
    var select = $(this.header()).find('select');
    select.on('change', function() {
      column.search($(this).val()).draw();
    });

    var options = column.data().unique().sort().toArray().map(function(d) {
      return '<option value="' + d + '">' + d + '</option>';
    });
    select.append(options);
  });

  $('.dataTables_filter').hide();
  var exerciseData = <?php echo json_encode($exercises); ?>;

  // Handle slider and label updates
  function handleSliderLabelUpdates() {
    $('.slider-container input[type="range"]').each(function() {
      var muscleName = $(this).attr('name');
      var intensity = $(this).val();
      $('#slider-value-' + muscleName).text(intensity);
      $(this).prev('.muscle-label').toggleClass('dot', intensity > 0);
    });
  }

  // Update muscle sliders when exercise is clicked
  $('#exercise-table tbody').on('click', 'tr', function() {
    var $this = $(this);
    $this.siblings().removeClass('selected');
    $this.toggleClass('selected', !$this.hasClass('selected'));
    var newExercise = !$this.hasClass('selected')
    // Toggle add/update/delete buttons
    $('#add-button').toggle(newExercise);
    $('#exercise-label').toggle(newExercise);
    $('#update-button, #delete-button').toggle(!newExercise);

    // Reset all sliders and labels to zero
    $('.slider-container input[type="range"]').val(0).prev('.muscle-label').removeClass('dot').find('span').text(0);

    if (!newExercise) {
      selectedExerciseId = $this.data('exercise-id');
      var exerciseId = $this.data('exercise-id');
      var exerciseName = $this.find('td:first-child').text();
      var muscles = exerciseData[exerciseName].muscles;
      $('#exercise-name').val(exerciseName);
      $('#exercise-type').val(exerciseData[exerciseName].type);
      $('#exercise-difficulty').val(exerciseData[exerciseName].difficulty);
      for (var muscleName in muscles) {
        if (muscles.hasOwnProperty(muscleName)) {
          var intensity = muscles[muscleName];
          $('#slider-' + muscleName).val(intensity);
          $('#slider-value-' + muscleName).text(intensity);
          $('#slider-' + muscleName).prev('.muscle-label').toggleClass('dot', intensity > 0);
        }
      }

      handleAjax('php/get_exercise_description.php', 'POST', {
        exercise_id: exerciseId
      }, function(response) {
        if (response) {
          $('#description').val(response);
        } else {
          $('#description').val('');
        }
      }, function(error) {
        console.error(error);
        $('#description').val('');
      });

    } else {
      selectedExerciseId = null;
      $('#exercise-name').val('');
      $('#exercise-type').val('');
      $('#exercise-difficulty').val('');
      $('#description').val('');
    }

    handleSliderLabelUpdates();
  });

  $('.slider-container input[type="range"]').on('input', handleSliderLabelUpdates);

  function handleAjax(url, type, data, successCallback, errorCallback) {
    $.ajax({
      url: url,
      type: type,
      data: data,
      success: successCallback,
      error: errorCallback
    });
  }

  $('#update-button').click(function() {
    var exerciseId = selectedExerciseId;
    var exerciseName = $('#exercise-name').val();
    var exerciseType = $('#exercise-type').val();
    var exerciseDifficulty = $('#exercise-difficulty').val();
    var description = $('#description').val();

    var muscles = [];
    $('.slider-container input[type="range"]').each(function() {
      var muscleName = $(this).attr('name');
      var intensity = $(this).val();
      if (intensity > 0) {
        var muscleId = muscleIds[muscleName];
        muscles.push({id: muscleId, intensity: intensity});
      }
    });

    if (!exerciseName || !isMuscleIntensitySet()) {
      alert('Please select an exercise.');
      return;
    }

    handleAjax('php/update_exercise.php', 'POST', {
      exerciseId: exerciseId,
      exerciseName: exerciseName,
      exerciseType: exerciseType,
      exerciseDifficulty: exerciseDifficulty,
      exerciseDescription: description,
      muscles: muscles
    }, function(response) {
      window.location.reload();
    }, function(error) {
      console.error(error);
      alert('An error occurred while updating the exercise.');
    });
  });


  $('#delete-button').click(function() {
    var exerciseId = $('#exercise-table tbody tr.selected').data('exercise-id');
    if (!exerciseId) {
      alert('Please select an exercise.');
      return;
    }

    if (confirm('Are you sure you want to delete this exercise?')) {
      handleAjax('php/delete_exercise.php', 'POST', {
        exerciseId: exerciseId
      }, function(response) {
        window.location.reload();
      }, function(error) {
        console.error(error);
        alert('An error occurred while deleting the exercise.');
      });
    }
  });

  $('#add-button').click(function() {
  var exerciseName = $('#exercise-name').val();
  var exerciseType = $('#exercise-type').val();
  var exerciseDifficulty = $('#exercise-difficulty').val();
  var description = $('#description').val();

  var muscles = [];

  // Get the muscle IDs and their intensities from the sliders
  $('.slider-container input[type="range"]').each(function() {
    var muscleName = $(this).attr('name');
    var intensity = $(this).val();
    if (intensity > 0) {
      var muscleId = muscleIds[muscleName];
      muscles.push({muscleId: muscleId, intensity: intensity});
    }
  });

  if (!exerciseName || !exerciseType || !exerciseDifficulty || !isMuscleIntensitySet()) {
    alert('Please enter an exercise name, type, difficulty, and at least one muscle intensity.');
    return;
  }

  console.log(exerciseName, exerciseType, exerciseDifficulty, description, muscles);

  handleAjax('php/add_exercise.php', 'POST', {
    exerciseName: exerciseName,
    exerciseType: exerciseType,
    exerciseDifficulty: exerciseDifficulty,
    description: description,
    muscles: muscles  // Include the muscle data array in the data sent
  }, function(response) {
    //window.location.reload();
  }, function(error) {
    console.error(error);
    alert('An error occurred while adding a new exercise.');
  });
});


});
</script>
</body>
</html>