<?php include 'php/header.php';
  require_once 'php/db_connect.php';
  require_once 'php/db_query.php';
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
  <title>ExerHub - Exercises</title>
  <link rel="stylesheet" type="text/css" href="//cdn.datatables.net/1.10.25/css/jquery.dataTables.min.css">
  <script type="text/javascript" src="//code.jquery.com/jquery-3.6.0.min.js"></script>
  <script type="text/javascript" src="//cdn.datatables.net/1.10.25/js/jquery.dataTables.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"></script>
  <link rel="stylesheet" href="css/style.css">
  <?php
    $result = query($conn, 'SELECT e.name AS exercise_name, e.type AS exercise_type, e.difficulty, m.name AS muscle_name, em.intensity
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
  <main class="container">
    <h2>Exercises</h2>
    <table id="exercise-table">
      <thead>
        <tr>
          <th>Name</th>
          <th class="type">Type:&nbsp;<select title="Filter by Type"><option value="">All Types</option></select></th>
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
  </main>
  <script src="js/nav.js"></script>
  <script>
    $(document).ready(function() {
      $('#exercise-table').DataTable({
        paging: false,
        searching: true,
        columnDefs: [
          {orderable: false, targets: [1]}
        ]
      }).column(1).every(function() {
        var column = this;
        var typeFilter = $(this.header()).find('select')
        $(document).on('change', '#exercise-table thead select', function() {
          column.search($(this).val()).draw();
        });
        column.data().unique().sort().each(function(d) {
          typeFilter.append('<option value="' + d + '">' + d + '</option>')
        });
      });
      $('.dataTables_filter').hide();
    });
  </script>
</body>
</html>
