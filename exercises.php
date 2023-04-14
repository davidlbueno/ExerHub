<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <link rel="stylesheet" href="style.css">
  <title>BWE - Exercises</title>
  <!-- Import Material UI scripts -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"></script>
</head>
<body class="dark">
  <!-- Navigation bar -->
  <nav>
    <div class="nav-wrapper">
      <a href="index.html" class="brand-logo">BWE</a>
      <a href="index.html" data-target="mobile-nav" class="sidenav-trigger"><i class="material-icons">menu</i></a>
      <ul class="right hide-on-med-and-down" id="desktop-nav"></ul>
    </div>
  </nav>
  <!-- Mobile navigation bar -->
  <ul class="sidenav" id="mobile-nav"></ul>
  <!-- Main content -->
  <main>
    <div class="container">
      <h2>Exercises</h2>
      <table>
        <tbody>
        <?php
          require_once('db.php');
          // Set filter variable
          $filter = isset($_GET['filter']) && $_GET['filter'] !== 'all' ? $_GET['filter'] : "push' OR exercises.type = 'pull' OR exercises.type = 'legs";
          // Get all distinct muscle names that are used in the query
          $query_muscles = "SELECT DISTINCT muscles.name FROM exercises 
                            INNER JOIN exercise_muscles ON exercises.id = exercise_muscles.exercise_id 
                            INNER JOIN muscles ON exercise_muscles.muscle_id = muscles.id";
          $result_muscles = query($query_muscles);
          $muscles = array_column(mysqli_fetch_all($result_muscles, MYSQLI_ASSOC), 'name');
          // Build the query for exercise and muscle data
          $query = "SELECT exercises.id, exercises.name, exercises.difficulty, ";
          foreach ($muscles as $muscle) {
            $query .= "MAX(CASE WHEN muscles.name = '$muscle' THEN exercise_muscles.intensity ELSE NULL END) AS $muscle, ";
          }
          $query = rtrim($query, ", ");
          $query .= " FROM exercises 
                      LEFT JOIN exercise_muscles ON exercises.id = exercise_muscles.exercise_id 
                      LEFT JOIN muscles ON exercise_muscles.muscle_id = muscles.id";
          $query .= $filter !== 'all' ? " WHERE exercises.type = '$filter'" : "";
          $query .= " GROUP BY exercises.id";
          // Execute the query and output the results
          $result = query($query);
          // Get the number of exercises for the selected filter
          $query_count = "SELECT COUNT(*) AS count FROM exercises" . ($filter === 'all' ? "" : " WHERE type = '$filter'");
          $count = mysqli_fetch_assoc(query($query_count))['count'];
          // Display the buttons and table headers
          echo "<div>
                  <button onclick=\"location.href='?filter=all'\">All Exercises</button>
                  <button onclick=\"location.href='?filter=push'\">Push Exercises</button>
                  <button onclick=\"location.href='?filter=pull'\">Pull Exercises</button>
                  <button onclick=\"location.href='?filter=legs'\">Legs Exercises</button>
                </div>
                <table>
                  <tr>
                    <th>Name</th>
                    <th style='background-color: #292929; text-align: center'>Difficulty</th>";
          foreach ($muscles as $muscle) {
            $query_check = "SELECT COUNT(*) AS count FROM exercises 
                            INNER JOIN exercise_muscles ON exercises.id = exercise_muscles.exercise_id 
                            INNER JOIN muscles ON exercise_muscles.muscle_id = muscles.id 
                            WHERE exercises.type = '$filter' AND muscles.name = '$muscle'";
            $count_check = mysqli_fetch_assoc(query($query_check))['count'];
            if ($count_check > 0) {
              echo "<th style='text-align: center'>$muscle</th>";
            }
          }
          echo "</tr>";
          // display the table data
          while ($row = mysqli_fetch_assoc($result)) {
            echo "<tr>";
            echo "<td>" . $row['name'] . "</td>";
            echo "<td style='font-weight:bold; text-align: center'>" . $row['difficulty'] . "</td>";
            foreach ($muscles as $muscle) {
              // check if the column should be displayed
              $query_check = "SELECT COUNT(*) AS count FROM exercises 
                INNER JOIN exercise_muscles ON exercises.id = exercise_muscles.exercise_id
                INNER JOIN muscles ON exercise_muscles.muscle_id = muscles.id
                WHERE exercises.type = '$filter' AND muscles.name = '$muscle'";
              $result_check = query($query_check);
              $count_check = mysqli_fetch_assoc($result_check)['count'];
              if ($count_check > 0) {
                echo "<td style='text-align: center'>" . $row[$muscle] . "</td>";
              }
            }
            echo "</tr>";
          }
          // display the number of exercises for the selected filter
          echo "<p>Total exercises: $count</p>";
        ?>
        </tbody>
      </table>
    </div>
  </main>
  <script src="nav.js"></script>
</body>
</html>
