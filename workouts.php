<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
  <meta charset="UTF-8" name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <title>BWE - Workouts</title>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"></script>
  <link rel="stylesheet" href="style.css">
  <?php require_once 'php/db.php'; ?>
</head>
<body class="dark">
<nav>
<div class="nav-wrapper">
  <span class="brand-logo" style="margin-left: 60px"><a href="index.html">BWE/</a><span class="sub-page-name">Workouts</span></span>
    <a href="index.html" data-target="side-nav" class="show-on-large sidenav-trigger"><i class="material-icons">menu</i></a>
    <ul class="right" id="top-nav"></ul>
</div>
</nav>
  <ul class="sidenav" id="side-nav"></ul>
  <main class="container">
  <div class="row">
    <div class="col s8">
    </div>
    <div class="col s2" style="width: 174px; align-items: center; margin-top: 10px;">
      <a href="create_workout.php" class="btn btn-floating waves-effect waves-light"><i class="material-icons">add</i></a><span style="margin-left: 5px;">Create Workout</span>
    </div>
  </div>
  <div class="row">
    <div class="col s12">
    <div class="col s8">
    <?php
        // Fetch and display the workouts for the current user
        session_start();
        $userId = $_SESSION['user_id'];
        
        $workouts = fetchWorkouts($userId);
        displayWorkouts($workouts);
        
        // Function to fetch workouts for the current user from the database
        function fetchWorkouts($userId) {
          global $conn;
          
          $query = "SELECT * FROM workouts WHERE user_id = $userId";
          $result = query($query);
          
          $workouts = array();
          while ($row = mysqli_fetch_assoc($result)) {
            $workouts[] = $row;
          }
          
          return $workouts;
        }
        
        // Function to display the fetched workouts
        function displayWorkouts($workouts) {
          if (empty($workouts)) {
            echo "<p>No workouts found.</p>";
          } else {
            echo "<ul>";
            foreach ($workouts as $workout) {
              echo "<li><a href='workout.php?workout_id=" . $workout['id'] . "&workout_name=" . urlencode($workout['name']) . "'>" . $workout['name'] . "</a></li>";


            }
            echo "</ul>";
          }
        }
      ?>
    </div>
  </main>
  <script src="js/nav.js"></script>
</body>
</html>
