<?php
$host = "127.0.0.1";
$user = "bwe";
$password = "buendavi";
$database = "bwe";

// Create connection
$conn = mysqli_connect($host, $user, $password, $database);

// Check connection
if (!$conn) {
  die("Connection failed: " . mysqli_connect_error());
}

function query($query) {
  global $conn;

  $result = mysqli_query($conn, $query);

  if (!$result) {
    die("Query failed: " . mysqli_error($conn));
  }

  return $result;
}

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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $query = $_POST['query'];
  $params = $_POST['params'];

  // Prepare the statement
  $stmt = mysqli_prepare($conn, $query);

  // Bind the parameters
  mysqli_stmt_bind_param($stmt, str_repeat('s', count($params)), ...$params);

  // Execute the statement
  mysqli_stmt_execute($stmt);

  // Check for errors
  if (mysqli_stmt_errno($stmt)) {
    echo "SQL Command Failed: " . mysqli_stmt_error($stmt);
  } else {
    echo "SQL Command completed successfully";
  }

  // Close the statement
  mysqli_stmt_close($stmt);
}

?>
