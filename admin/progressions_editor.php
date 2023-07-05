<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
  <meta charset="UTF-8" name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <title>ExerHub - Admin: Progressions Editor</title>
  <link rel="stylesheet" type="text/css" href="//cdn.datatables.net/1.10.25/css/jquery.dataTables.min.css">
  <script type="text/javascript" src="//code.jquery.com/jquery-3.6.0.min.js"></script>
  <script type="text/javascript" src="//cdn.datatables.net/1.10.25/js/jquery.dataTables.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"></script>
  <link rel="stylesheet" href="../style.css">
  <link rel="stylesheet" href="admin.css">
  <?php 
    require_once '../php/db.php'; 
    $exercises = queryExercises();
    $muscles = query('SELECT * FROM muscles');
    function queryExercises() {
        $result = query('SELECT e.id, e.name AS exercise_name, e.type AS exercise_type, e.difficulty, m.name AS muscle_name, em.intensity
            FROM exercises e
            JOIN exercise_muscles em ON e.id = em.exercise_id
            JOIN muscles m ON m.id = em.muscle_id');
        $exercises = array();
        while ($row = mysqli_fetch_assoc($result)) {
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
      <div id="cancel-add-item" style="text-align: center; background-color: #a10000; display: none;">Select Exercises to add to progression<button>cancel</button></div>
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
          <ul id="exercise-items"></ul>
          <div style="text-align: center;">
            <button class="btn" id="add-btn" style="display: none;">Add Exercise</button>
            <button class="btn" id="cancel-btn" style="display: none;">Cancel</button>
          </div>
        </div>
      </div>
    </div>
  </main>
  <script src="../js/nav.js"></script>
  <script>
    $(document).ready(function() {
    let addingExercise = false;
    let exerciseTable = $('#exercise-table').DataTable({
      paging: false,
      searching: true,
      columnDefs: [{ orderable: false, targets: [1] }]
    });

    exerciseTable.column(1).every(function() {
      let column = this;
      $(this.header()).find('select').on('change', function() {
        column.search($(this).val()).draw();
      }).append(column.data().unique().sort().toArray().map(d => '<option value="' + d + '">' + d + '</option>'));
    });

    $('.dataTables_filter').hide();
    let exerciseData = <?php echo json_encode($exercises); ?>;

    function handleExerciseTableClick() {
      if (!addingExercise) {
        let exerciseName = exerciseTable.row(this).data()[0];
        let exerciseId = $(this).find('input[name="exercise_id"]').val();
        // show the add item button
        $('#add-btn').css('display', 'inline-block');
        $('#cancel-btn').css('display', 'inline-block');
        $('#selected-exercise-name').text(exerciseName);
        let query = "SELECT p.id, e.name FROM progressions p JOIN exercises e ON p.exercise_id = e.id WHERE p.exercise_id = ?";
        let params = [exerciseId];
        $.post('../php/db.php', { query, params }, null, 'json')
          .done((data) => {
            let exerciseItems = "";
            data.forEach((progression) => { 
              exerciseItems += "<li>" + progression.name + "<button class='delete-btn' data-progression-id='" + progression.id + "'>Delete</button></li>";
            });
            $('#exercise-items').html(exerciseItems);
            if (data.length === 0) {
              $('#exercise-items').html("<li>There are no progressions for this exercise.</li>");
            }
          })
          .fail((err) => {
            console.log(err);
          });
      }
    }

    // Bind the click event for 'tr'
    $('#exercise-table tbody').on('click', 'tr', handleExerciseTableClick);

    $('#add-btn').click(function() {
      $('#exercise-table').css('border', '2px solid red');
      addingExercise = true;
      $('#cancel-add-item').css('display', 'block');
      $('#cancel-btn').css('display', 'none');
      $('#add-btn').css('display', 'none');
      $('#exercise-table tbody').on('click', 'tr', function() {
        if (addingExercise) {
          let exerciseName = exerciseTable.row(this).data()[0];
          let exerciseId = $(this).find('input[name="exercise_id"]').val();
          $('#exercise-items').append("<li>" + exerciseName + "<button class='delete-btn' data-exercise-id='" + exerciseId + "'>Delete</button></li>");
          $('#exercise-items li:contains("There are no progressions for this exercise.")').remove();
        }
      });
    });

    $('#cancel-add-item button').click(function() {
      $('#exercise-table').css('border', 'none');
      addingExercise = false;
      $('#cancel-add-item').css('display', 'none');
      $('#cancel-btn').css('display', 'inline-block');
      $('#exercise-table tbody').off('click', 'tr', handleExerciseTableClick);
      $('#add-btn').css('display', 'inline-block');
    });

    // remove Progression list item from the list only when its delete button is clicked
    $('#exercise-items').on('click', '.delete-btn', function() {
      $(this).parent().remove();
      if ($('#exercise-items').children().length === 0) {
        $('#exercise-items').html("<li>There are no progressions for this exercise.</li>");
      }
    });

    // cancel button removes the selected exercises from the list, resets the selected exercise name, and hides the cancel button
    $('#cancel-btn').click(function() {
      $('#exercise-items').html("");
      $('#selected-exercise-name').text("");
      $('#cancel-btn').css('display', 'none');
      $('#add-btn').css('display', 'none');
      addingExercise = false;

      // Rebind the click event for 'tr'
      $('#exercise-table tbody').on('click', 'tr', handleExerciseTableClick);
    });
  });
  </script>
</body>
</html>
