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

// check if a filter has been selected
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';
// get all distinct muscle names that are used in the query
$query_muscles = "SELECT DISTINCT muscles.name FROM exercises 
INNER JOIN exercise_muscles ON exercises.id = exercise_muscles.exercise_id 
INNER JOIN muscles ON exercise_muscles.muscle_id = muscles.id";
$result_muscles = query($query_muscles);
$muscles = array();
while ($row_muscles = mysqli_fetch_assoc($result_muscles)) {
  $muscles[] = $row_muscles['name'];
}
// build the query for exercise and muscle data
$query = "SELECT exercises.id, exercises.name, exercises.difficulty, ";
foreach ($muscles as $muscle) {
  $query .= "MAX(CASE WHEN muscles.name = '$muscle' THEN exercise_muscles.intensity ELSE NULL END) AS $muscle, ";
}
$query = rtrim($query, ", "); // remove the trailing comma and space
$query .= " FROM exercises 
INNER JOIN exercise_muscles ON exercises.id = exercise_muscles.exercise_id 
INNER JOIN muscles ON exercise_muscles.muscle_id = muscles.id";
// apply the filter if one has been selected
if ($filter !== 'all') {
  $query .= " WHERE exercises.type = '$filter'";
}
$query .= " GROUP BY exercises.id";
// execute the query and output the results
$result = query($query);
echo "<div>
  <button onclick=\"location.href='?filter=all'\">All Exercises</button>
  <button onclick=\"location.href='?filter=push'\">Push Exercises</button>
  <button onclick=\"location.href='?filter=pull'\">Pull Exercises</button>
  <button onclick=\"location.href='?filter=legs'\">Legs Exercises</button>
</div>";
echo "<table>";
echo "<tr><th>Name</th><th>Difficulty</th>";
foreach ($muscles as $muscle) {
  echo "<th>$muscle</th>";
}
echo "</tr>";
while ($row = mysqli_fetch_assoc($result)) {
  echo "<tr>";
  echo "<td>" . $row['name'] . "</td>";
  echo "<td>" . $row['difficulty'] . "</td>";
  foreach ($muscles as $muscle) {
    echo "<td>" . $row[$muscle] . "</td>";
  }
  echo "</tr>";
}
echo "</table>";
?>
        </tbody>
      </table>
    </div>
  </main>
  <script src="nav.js"></script>
</body>
</html>
