<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
  <meta charset="UTF-8" name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <title>BWE - Workout Details</title>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"></script>
  <link rel="stylesheet" href="style.css">
  <?php require_once 'php/db.php'; ?>
</head>
<body class="dark">
<nav>
  <div class="nav-wrapper">
    <span class="brand-logo" style="margin-left: 60px"><a href="index.html">BWE/</a><a href="workouts.php">Workouts/</a><span class="sub-page-name">Workout</span></span>
    <a href="index.html" data-target="side-nav" class="show-on-large sidenav-trigger"><i class="material-icons">menu</i></a>
    <ul class="right" id="top-nav"></ul>
  </div>
</nav>
<ul class="sidenav" id="side-nav"></ul>
<main class="container">
  <div class="row">
    <div class="col s12">
      <div class="col s8">
        <?php
          session_start();
          $userId = $_SESSION['user_id'];
          $workoutId = $_GET['workout_id'];
          $workout_name = $_GET['workout_name'];
          echo "<h5>$workout_name</h5>";

          $workoutItems = fetchWorkouts($workoutId);
          displayWorkoutDetails($workoutItems);
        
          function fetchWorkouts($workoutId) {
            global $conn;            
            $query = "SELECT * FROM workout_sequences WHERE workout_id = $workoutId";
            $result = query($query);
            $items = array();
            while ($row = mysqli_fetch_assoc($result)) {
              $items[] = $row;
            }
            
            return $items;
          }

          function displayWorkoutDetails($items) {
            if (empty($items)) {
              echo "<p>No workout found.</p>";
            } else {
              
              foreach ($items as $item) {
                echo "<h6>" . $item['type'] . " " . $item['exercise'] . " " . $item['seconds'] . " " . $item['sets'] . "</h6>";
                // Display other workout details here
              }
            }
          }
        ?>
      </div>
    </div>
  </div>
  <div>
    <button id="startButton" data-target="timerModal" class="btn">Start</button>
  </div>
  <div id="timerModal" class="modal">
   <div class="modal-content">
    <h4>Workout Timer</h4>
    <p id="exerciseName"></
    <button id="startButton" data-target="timerModal" class="btn">Start</button>
    <div id="timerModal" class="modal">
      <div class="modal-content">
        <h4>Workout Timer</h4>
        <p id="exerciseName"></p>
        <p id="timer"></p>
      </div>
      <div class="modal-footer">
        <a href="#!" class="modal-close waves-effect waves-green btn-flat">Close</a>
      </div>
    </div>
</main>
<script>
  document.addEventListener('DOMContentLoaded', function() {
    var startButton = document.getElementById('startButton');
    var timerModal = document.getElementById('timerModal');
    var exerciseName = document.getElementById('exerciseName');
    var timer = document.getElementById('timer');
    var workoutItems = <?php echo json_encode($workoutItems); ?>;

    var currentIndex = 0;
    var countdownInterval;

    startButton.addEventListener('click', function() {
      currentIndex = 0;
      startCountdown();
      var modalInstance = M.Modal.init(timerModal); // Initialize the modal instance
      modalInstance.open(); // Open the modal
    });

    function startCountdown() {
      if (currentIndex < workoutItems.length) {
        var currentItem = workoutItems[currentIndex];
        exerciseName.textContent = currentItem['exercise'];
        var seconds = currentItem['seconds'];
        var count = seconds;

        timer.textContent = formatTime(count);

        countdownInterval = setInterval(function() {
          count--;
          timer.textContent = formatTime(count);

          if (count === 0) {
            clearInterval(countdownInterval);
            currentIndex++;
            startCountdown();
          }
        }, 1000);
      }
    }

    function formatTime(seconds) {
      var minutes = Math.floor(seconds / 60);
      var remainingSeconds = seconds % 60;

      return minutes.toString().padStart(2, '0') + ':' + remainingSeconds.toString().padStart(2, '0');
    }
  });
</script>
<script src="js/nav.js"></script>
</body>
</html>
