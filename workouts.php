<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
  <?php include 'php/header.php'; ?>
  <title>ExerHub - Workouts</title>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"></script>
  <link rel="stylesheet" href="style.css">
  <?php require_once 'php/db.php'; ?>
</head>
<body class="dark">
<nav>
<div class="nav-wrapper">
  <span class="brand-logo" style="margin-left: 60px"><a href="index.html"><i class="material-icons">home</i>/</a><span class="sub-page-name">Workouts</span></span>
    <a href="index.html" data-target="side-nav" class="show-on-large sidenav-trigger"><i class="material-icons">menu</i></a>
    <ul class="right" id="top-nav"></ul>
</div>
</nav>
  <ul class="sidenav" id="side-nav"></ul>
  <main class="container">
  <div class="row">
  <div class="col s12" style="text-align: center; margin: 5px; display: flex; justify-content: center; flex-wrap: wrap;">
    <a href="select_workouts.php"><button class="btn" id="selectWorkoutBtn" style="margin: 5px; line-height: 1;">Select Workout</button></a>
    <a href="create_workout.php"><button class="btn" id="createWorkoutBtn" style="margin: 5px; line-height: 1;">Create Workout</button></a>
    <a href="create_workout.php"><button class="btn" id="workoutHistoryBtn" style="margin: 5px; line-height: 1;">Workout History</button></a>
  </div>
  <div class="row">
    <div class="col s12">
      <h6>My Workouts:</h6>
    <div class="col s12">
    <?php
        // Fetch and display the workouts for the current user
        session_start();
        if (isset($_SESSION['user_id'])) {
            $userId = $_SESSION['user_id'];
            $workouts = query("SELECT * FROM workouts WHERE id IN (SELECT workout_id FROM user_selected_workouts WHERE user_id = $userId)");
        } else {
            $workouts = query("SELECT * FROM workouts WHERE is_public = 1");
        }
        displayWorkouts($workouts);
        // Function to display the fetched workouts
        function displayWorkouts($workouts) {
          if (mysqli_num_rows($workouts) == 0) {
            echo "<p>No workouts found.</p>";
          } else {
            echo "<ul>";
            while ($workout = mysqli_fetch_assoc($workouts)) {
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
