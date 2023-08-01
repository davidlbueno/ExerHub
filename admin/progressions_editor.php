<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
  <?php include '../php/header.php';
    require_once '../php/db_connect.php';
    require_once '../php/db_query.php';

    session_start();
    $userId = $_SESSION['user_id'];
    $is_admin = $_SESSION['is_admin'];
  ?>
  <script type="text/javascript" src="//code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://code.jquery.com/ui/1.13.0/jquery-ui.min.js"></script>
  <script type="text/javascript" src="//cdn.datatables.net/1.10.25/js/jquery.dataTables.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"></script>
  <link rel="stylesheet" href="../style.css">
  <link rel="stylesheet" href="css/admin.css">
  <?php 
        $exercises = queryExercises($conn);
        $muscles = query($conn, 'SELECT * FROM muscles');

        function queryExercises($conn) {
            $result = query($conn, 'SELECT e.id, e.name AS exercise_name, e.type AS exercise_type, e.difficulty, m.name AS muscle_name, em.intensity
                FROM exercises e
                JOIN exercise_muscles em ON e.id = em.exercise_id
                JOIN muscles m ON m.id = em.muscle_id');
            $exercises = array();
            foreach ($result as $row) {
                $exerciseId = $row['id'];
                $exerciseName = $row['exercise_name'];
                $muscleName = $row['muscle_name'];
                $intensity = $row['intensity'];
                $exerciseType = $row['exercise_type'];
                $exerciseDifficulty = $row['difficulty'];
                if (!isset($exercises[$exerciseName])) {
                    $exercises[$exerciseName] = array(
                        'id' => $exerciseId,
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
      <span class="brand-logo" style="margin-left: 60px"><a href="index.html"><i class="material-icons">home</i></a><a href="/admin/index.html">/Admin/</a><span class="sub-page-name">Progressions Editor</span></span>
      <a href="index.html" data-target="side-nav" class="show-on-large sidenav-trigger"><i class="material-icons">menu</i></a>
      <ul class="right" id="top-nav"></ul>
    </div>
  </nav>
  <ul class="sidenav" id="side-nav"></ul>
  <main class="container" style="display: flex; flex-direction: column;">
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
              <tr>
                <td><?= htmlspecialchars($exerciseName) ?></td>
                <td><?= htmlspecialchars($exerciseData['type']) ?></td>
                <td><?= htmlspecialchars($exerciseData['difficulty']) ?></td>
                <td>
                  <?php foreach ($exerciseData['muscles'] as $muscleName => $intensity): ?>
                    <span><?= htmlspecialchars($muscleName) ?></span> (<?= htmlspecialchars($intensity) ?>)<br>
                  <?php endforeach; ?>
                </td>
                <input type="hidden" name="exercise_id" value="<?= htmlspecialchars($exerciseData['id']) ?>">
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <div class="right-column" style="height: 80vh; width: 50%; box-sizing: border-box; overflow-y: auto;">
        <div>
          <h4 id="selected-exercise-name" style="text-align: center; width: 100%; margin: 0;"></h4>
          <hr style="width: 100%; margin: 0;">
          <ol id="exercise-items" style="padding: 5px 0 5px 0;"></ol>
          <span id="already-added" style='color: red; display: none;'>This exercise is already in the list.</span>
          <span id="no-progressions" style='color: red; display: none;'>There are no progressions for this exercise.</span>
          <div style="text-align: center;">
            <button class="btn" id="add-btn" style="display: none;">Add</button>
            <button class="btn" id="cancel-btn" style="display: none;">Cancel</button>
            <button class="btn" id="done-adding-items" style="display: none;">Done Adding Exercises</button>
            <button class="btn" id="save-btn" style="display: none;">Save</button>
            <button class="btn" id="delete-btn" style="display: none;">Delete</button>
          </div>
        </div>
      </div>
    </div>
  </main>
  <script src="../js/nav.js"></script>
  <script>
  $(document).ready(function() {
  let addingExercise = false;
  let selectedExerciseId = null;
  let exerciseTable = $('#exercise-table').DataTable({
    paging: false,
    searching: true,
    columnDefs: [{ orderable: false, targets: [1] }]
  });
  let exerciseItems = $('#exercise-items');

  exerciseTable.column(1).every(function() {
    let column = this;
    $(this.header()).find('select').on('change', function() {
      column.search($(this).val()).draw();
    }).append(column.data().unique().sort().toArray().map(d => $('<option>', { value: d, text: d })));
  });

  $('.dataTables_filter').hide();
  let exerciseData = <?php echo json_encode($exercises); ?>;

  function handleExerciseTableClick() {
    if (!addingExercise) {
        let exerciseName = $(this).find('td:first-child').text();
        selectedExerciseId = $(this).find('input[name="exercise_id"]').val();
        $('#add-btn, #cancel-btn, #save-btn').css('display', 'inline-block');
        $('#selected-exercise-name').text(exerciseName);
        let query = "SELECT e.name, p.progression_exercise_id, p.threshold, p.sequence_order FROM progressions p JOIN exercises e ON p.progression_exercise_id = e.id WHERE p.exercise_id = ?";
        let params = [selectedExerciseId];
        $.post('../php/db_query.php', { query, params }, null, 'json')
            .done((data) => {
                data.sort((a, b) => a.sequence_order - b.sequence_order); // Sort by sequence_order
                let exerciseItemsHtml = data.map((progressionExercise) => {
                    return `
                    <li class='exercise-item' data-progression-exercise-id='${progressionExercise.progression_exercise_id}'>
                      <div style='display: flex; flex-wrap: wrap; align-items: flex-start; justify-content: space-between;'>
                        <span id='exercise-name' style='display: inline-block; margin-left: 5px;'>${progressionExercise.name}</span>
                        <div style='text-align: right;'>
                          <label for='threshold' style='margin: 2px 5px;'>Reps Threshold:</label>
                          <input id='threshold' type='number' min='1' max='10' value='${progressionExercise.threshold}' style='width: 40px; height: 22px; margin-right: 5px;'>
                          <button id='del-item-btn' class='copy-del-btn'>Delete</button>
                        </div>
                      </div>
                    </li>`;
                }).join('');
                exerciseItems.html(exerciseItemsHtml);
                $('#no-progressions').css('display', data.length === 0 ? 'block' : 'none');
            })
            .fail((err) => {
                console.log("ERROR: " + err);
            });
    }
  }

  function addExerciseItem(exerciseName, exerciseId) {
    let exerciseItemsList = exerciseItems.find('.exercise-item');
    let selectedExerciseName = $('#selected-exercise-name').text();
    let exerciseExists = exerciseItemsList.toArray().some((item) => {
      return item.innerText.includes(exerciseName);
    });
    if (selectedExerciseName !== "" && selectedExerciseName !== exerciseName && !exerciseExists) {
      let exerciseItem = `
      <li class='exercise-item' data-progression-exercise-id='${exerciseId}'>
        <div style='display: flex; flex-wrap: wrap; align-items: flex-start; justify-content: space-between;'>
          <span id='exercise-name' style='display: inline-block; margin-left: 5px;'>${exerciseName}</span>
          <div style='text-align: right;'>
            <label for='threshold' style='margin: 2px 5px;'>Reps Threshold:</label>
            <input id='threshold' type='number' min='1' max='10' value='1' style='width: 40px; height: 22px; margin-right: 5px;'>
            <button id='del-item-btn' class='copy-del-btn'>Delete</button>
          </div>
        </div>
      </li>`;
      exerciseItems.append(exerciseItem);
      $('#no-progressions, #already-added').css('display', 'none');
    } else {
      $('#already-added').css('display', 'block');
      $('#no-progressions').css('display', 'none');
    }
  }

  function removeExerciseItem() {
    $(this).closest('li').remove();
    if (exerciseItems.children().length === 0) {
      exerciseItems.html("<li>There are no progressions for this exercise.</li>");
    }
  }

  function saveExerciseProgressions() {
  let exerciseItemsList = exerciseItems.find('.exercise-item');
  let progressionExercises = [];

  let query = "SELECT * FROM progressions WHERE exercise_id = ?";
  let params = [selectedExerciseId];
  $.post('../php/db_post.php', { query, params }, null, 'json')
    .done((data) => {
      let progressions = data;
      function saveExercise(i) {
        if (i >= exerciseItemsList.length) return;
        let exerciseItem = $(exerciseItemsList[i]);
        let exerciseName = exerciseItem.find('#exercise-name').text();
        let repsThreshold = exerciseItem.find('input[type="number"]').val();
        let exerciseId = parseInt(exerciseItem.data('progression-exercise-id'));
        let nextExerciseId = (i < exerciseItemsList.length - 1) ? parseInt(exerciseItemsList[i + 1].dataset.progressionExerciseId) : 0;
        let listItemNumber = i + 1;
        progressionExercises.push({
          exerciseName,
          exerciseId,
          repsThreshold,
          nextExerciseId
        });
        let existingRecord = progressions.find((record) => record.progression_exercise_id === exerciseId);
        if (existingRecord) {
          let updateQuery = "UPDATE progressions SET threshold = ?, sequence_order = ?, next_exercise_id = ? WHERE exercise_id = ? AND progression_exercise_id = ?";
          let updateParams = [repsThreshold, listItemNumber, nextExerciseId, selectedExerciseId, exerciseId];
          $.post('../php/db_post.php', { query: updateQuery, params: updateParams }, null, 'json')
            .done((data) => {
              console.log(data);
              saveExercise(i + 1);
            })
            .fail((err) => {
              console.log("ERROR: " + err);
              saveExercise(i + 1);
            });
        } else {
          let insertQuery = "INSERT INTO progressions (exercise_id, progression_exercise_id, sequence_order, next_exercise_id, threshold) VALUES (?, ?, ?, ?, ?)";
          let insertParams = [selectedExerciseId, exerciseId, listItemNumber, nextExerciseId, repsThreshold];
          $.post('../php/db_post.php', { query: insertQuery, params: insertParams }, null, 'json')
            .done((data) => {
              console.log(data);
              saveExercise(i + 1);
            })
            .fail((err) => {
              console.log("ERROR: " + err);console.log(err);
              saveExercise(i + 1);
            });
        }
      }
      let deletedItems = progressions.filter((record) => {
        return !Array.from(exerciseItemsList).some((item) => parseInt(item.dataset.progressionExerciseId) === record.progression_exercise_id);
      });
      if (deletedItems.length > 0) {
        let deleteParams = [selectedExerciseId, ...deletedItems.map((item) => item.progression_exercise_id)];
        let placeholders = deletedItems.map(() => "?").join(", ");
        let deleteQuery = `DELETE FROM progressions WHERE exercise_id = ? AND progression_exercise_id IN (${placeholders})`;
        $.post('../php/db_post.php', { query: deleteQuery, params: deleteParams }, null, 'json')
          .done((data) => {
            console.log(data);
            saveExercise(0);
          })
          .fail((err) => {
            console.log("ERROR: " + err);
            saveExercise(0);
          });
      } else {
        saveExercise(0);
      }
    })
    .fail((err) => {
      console.log("ERROR: " + err);
    });
  }

  function deleteExerciseProgressions() {
  let query = "DELETE FROM progressions WHERE exercise_id = ?";
  let params = [selectedExerciseId];
  $.post('../php/db_post.php', { query, params }, null, 'json')
    .done((data) => {
      console.log(data);
      exerciseItems.html("");
      $('#selected-exercise-name').text("");
      $('#cancel-btn, #save-btn, #add-btn').css('display', 'none');
      addingExercise = false;
      $('#exercise-table tbody').off('click', 'tr');
      $('#exercise-table tbody').on('click', 'tr', handleExerciseTableClick);
    })
    .fail((err) => {
      console.log("ERROR: " + err);
    });
  }

  function cancelAddingExercise() {
    exerciseItems.html("");
    $('#selected-exercise-name').text("");
    $('#cancel-btn, #save-btn, #add-btn, #delete-btn').css('display', 'none');
    addingExercise = false;
    $('#exercise-table tbody').off('click', 'tr');
    $('#exercise-table tbody').on('click', 'tr', handleExerciseTableClick);
  }

  function initializeSorting() {
    exerciseItems.sortable({
      axis: 'y',
      containment: 'parent'
    });
  }

  // Bind the click event for 'tr'
  $('#exercise-table tbody').on('click', 'tr', handleExerciseTableClick);
  $('#add-btn').click(function() {
    $('#exercise-table').css('border', '2px solid red');
    addingExercise = true;
    $('#done-adding-items').css('display', 'inline-block');
    $('#cancel-btn, #add-btn, #delete-btn').css('display', 'none');
    $('#exercise-table tbody').off('click', 'tr', handleExerciseTableClick);
    $('#exercise-table tbody').on('click', 'tr td', function() {
      if (addingExercise) {
        let exerciseName = $(this).closest('tr').find('td:first-child').text();
        let exerciseId = $(this).closest('tr').find('input[name="exercise_id"]').val();
        addExerciseItem(exerciseName, exerciseId);
      }
    });
  });

  $('#done-adding-items').click(function() {
    $('#exercise-table').css('border', 'none');
    addingExercise = false;
    $('#done-adding-items').css('display', 'none');
    $('#cancel-btn, #save-btn').css('display', 'inline-block');
    $('#exercise-table tbody').off('click', 'tr td');
    $('#exercise-table tbody').on('click', 'tr', handleExerciseTableClick);
    $('#add-btn, #save-btn, #delete-btn').css('display', 'inline-block');
    $('#no-progressions, #already-added').css('display', 'none');
  });

  exerciseItems.on('click', '#del-item-btn', removeExerciseItem);
  $('#save-btn').click(saveExerciseProgressions);
  $('#delete-btn').click(deleteExerciseProgressions);
  $('#cancel-btn').click(cancelAddingExercise);
  initializeSorting();
});
</script>
</body>
</html>
