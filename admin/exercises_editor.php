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
  <?php require_once '../php/db.php'; ?>
  <?php
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

    $muscles = query('SELECT * FROM muscles');
  ?>
  <style>
    .container {
      display: flex;
    }

    .left-column {
      flex: 1;
      padding-right: 20px;
    }

    .right-column {
      flex: 1;
    }

    #exercise-table th:first-child,
    #exercise-table td:first-child {
      width: 40%;
    }

    #exercise-table th.type,
    #exercise-table td.type {
      width: 20%;
    }

    .slider-container {
      margin-top: 20px;
    }
  </style>
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
  <main class="container">
    <div class="left-column">
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
              <td><?= $exerciseName ?></td>
              <td><?= $exerciseData['type'] ?></td>
              <td><?= $exerciseData['difficulty'] ?></td>
              <td>
                <?php foreach ($exerciseData['muscles'] as $muscleName => $intensity): ?>
                  <span><?= $muscleName ?></span> (<?= $intensity ?>)<br>
                <?php endforeach; ?>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <div class="right-column">
      <?php foreach ($muscles as $muscle): ?>
        <div class="slider-container">
          <label for="slider-<?= $muscle['name'] ?>"><?= $muscle['name'] ?>: <span id="slider-value-<?= $muscle['name'] ?>"></span></label>
          <input type="range" id="slider-<?= $muscle['name'] ?>" name="<?= $muscle['name'] ?>" min="0" max="10" value="<?= isset($muscle['intensity']) ? $muscle['intensity'] : '0' ?>">
        </div>
      <?php endforeach; ?>
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

      // Update muscle sliders when exercise is clicked
      $('#exercise-table tbody').on('click', 'tr', function() {
        var exerciseName = $(this).find('td:first-child').text();
        var exerciseData = <?php echo json_encode($exercises); ?>;
        var muscles = exerciseData[exerciseName].muscles;

        // Reset all sliders to zero
        $('.slider-container input[type="range"]').val(0);

        for (var muscleName in muscles) {
          if (muscles.hasOwnProperty(muscleName)) {
            var intensity = muscles[muscleName];
            $('#slider-' + muscleName).val(intensity);
            $('#slider-value-' + muscleName).text(intensity);
          }
        }
      });

      // Update muscle name labels with slider values
      $('.slider-container input[type="range"]').on('input', function() {
        var muscleName = $(this).attr('name');
        var intensity = $(this).val();
        $('#slider-value-' + muscleName).text(intensity);
      });
    });
  </script>
</body>
</html>
