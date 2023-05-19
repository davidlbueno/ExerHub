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
          echo "<h5 id='workoutName'>$workout_name</h5>";
          $workoutItems = fetchWorkoutItems($workoutId);
          displayWorkoutDetails($workoutItems);

          function fetchWorkoutItems($workoutId)
          {
            global $conn;
            $query = "SELECT ws.type, ws.exercise_id, ws.seconds, ws.sets, e.name as exercise_name FROM workout_sequences ws LEFT JOIN exercises e ON ws.exercise_id = e.id WHERE ws.workout_id = $workoutId";
            $result = query($query);
            $items = array();
            while ($row = mysqli_fetch_assoc($result)) {
              $items[] = $row;
            }
            return $items;
          }

          function displayWorkoutDetails($items)
          {
            if (empty($items)) {
              echo "<p>No workout found.</p>";
            } else {
              foreach ($items as $item) {
                $exerciseName = $item['exercise_name'] ?: '';
                echo "<h6>{$item['type']} {$exerciseName} {$item['seconds']} {$item['sets']}</h6>";
                // Display other workout details here
              }
            }
          }
          ?>

          <button id="startButton" class="btn">Start</button>
          <div id="timerModal" class="modal">
            <div class="modal-content">
              <div class="modal-title">
                <h4>Workout Timer</h4>
                <div class="modal-buttons">
                  <button id="playButton" class="btn-flat"><i class="material-icons">play_arrow</i></button>
                  <button id="skipButton" class="btn-flat"><i class="material-icons">skip_next</i></button>
                  <button id="restartButton" class="btn-flat"><i class="material-icons">replay</i></button>
                  <button id="closeButton" class="modal-close btn-flat"><i class="material-icons">close</i></button>
                </div>
              </div>
              <ul id="workoutList"></ul>
            </div>
          </div>
        </div>
      </div>
    </div>
  </main>
  <script>
    var workoutItems = <?php echo json_encode($workoutItems); ?>;
    document.addEventListener('DOMContentLoaded', function() {
      var startButton = document.getElementById('startButton');
      var timerModal = document.getElementById('timerModal');
      var workoutList = document.getElementById('workoutList');
      var playButton = document.getElementById('playButton');
      var skipButton = document.getElementById('skipButton');
      var restartButton = document.getElementById('restartButton');
      var closeButton = document.getElementById('closeButton');
      var currentIndex = 0;
      var interval;
      var isPaused = true;
      var remainingTime = 0;

      startButton.addEventListener('click', startWorkout);
      playButton.addEventListener('click', togglePlay);
      skipButton.addEventListener('click', skipCountdown);
      restartButton.addEventListener('click', restartCountdown);
      closeButton.addEventListener('click', resetTimer);

      function startWorkout() {
        workoutList.innerHTML = '';

        workoutItems.forEach(function(item) {
          for (var i = 0; i < item.sets; i++) {
            workoutList.appendChild(createListItem(item));
          }
        });

        var modalInstance = M.Modal.init(timerModal);
        modalInstance.open();
        modalInstance.options.onCloseEnd = resetTimer;

        // Set workout name as modal title
        var workoutName = document.getElementById('workoutName').innerText;
        var modalTitle = document.querySelector('#timerModal .modal-title h4');
        modalTitle.textContent = workoutName;
      }

      function createListItem(item) {
        var li = document.createElement('li');
        var seconds = Number(item.seconds);
        var text = document.createTextNode(item.type + ' ' + (item.exercise_name || '') + ' ' + seconds.toFixed(2) + ' seconds');
        li.appendChild(text);
        return li;
      }

      function countdown(item) {
          var seconds = remainingTime > 0 ? remainingTime : parseFloat(item.seconds);
          var element = workoutList.children[currentIndex];

        interval = setInterval(function() {
            element.textContent = item.type + ' ' + (item.exercise_name || '') + ' ' + seconds.toFixed(2) + ' seconds';

          if (seconds <= 0) {
            clearInterval(interval);
            currentIndex++;
            remainingTime = 0;

            if (currentIndex < workoutItems.length) {
              countdown(workoutItems[currentIndex]);
              // Highlight the next list item
              workoutList.children[currentIndex].classList.add('highlighted');
            } else {
              // All items have been counted down
              // You can add your desired action here
            }
          }

          seconds -= 0.01;
        }, 10); // Run the interval every 10 milliseconds (hundredths of a second)

      // Highlight the current list item
      element.classList.add('highlighted');
      }

      function togglePlay() {
        if (isPaused) {
          playCountdown();
        } else {
          pauseCountdown();
        }
      }

      function playCountdown() {
        if (currentIndex < workoutItems.length) {
          var item = workoutItems[currentIndex];
          countdown(item);
          isPaused = false;
          playButton.innerHTML = '<i class="material-icons">pause</i>';
        }
      }

      function pauseCountdown() {
        clearInterval(interval);
        isPaused = true;
        playButton.innerHTML = '<i class="material-icons">play_arrow</i>';
        var currentElement = workoutList.children[currentIndex];
        var timeText = currentElement.textContent;
        var timeArray = timeText.split(' ');
        remainingTime = parseFloat(timeArray[timeArray.length - 2]);
      }

      function skipCountdown() {
        clearInterval(interval);

        // Remove the highlight from the current list item
        workoutList.children[currentIndex].classList.remove('highlighted');

        currentIndex++;
        remainingTime = 0;

        if (currentIndex < workoutItems.length) {
          countdown(workoutItems[currentIndex]);
          // Highlight the next list item
          workoutList.children[currentIndex].classList.add('highlighted');
        } else {
          // All items have been counted down
          // You can add your desired action here
        }
      }

      function restartCountdown() {
        clearInterval(interval);
        currentIndex = 0;
        remainingTime = 0;

        // Reset 'seconds' values for each list item
        workoutItems.forEach(function(item) {
          item.seconds = parseFloat(item.seconds);
        });

        // Update displayed list items with new 'seconds' values
        var listItems = workoutList.getElementsByTagName('li');
        for (var i = 0; i < listItems.length; i++) {
          var item = workoutItems[i];
          if (item) {
            var seconds = Number(item.seconds);
            var text = item.type + ' ' + (item.exercise_name || '') + ' ' + seconds.toFixed(2) + ' seconds';
            listItems[i].textContent = text;
          }
        }

        pauseCountdown();
      }

      function resetTimer() {
        currentIndex = 0;
        clearInterval(interval);
        isPaused = true;
        remainingTime = 0;
        playButton.innerHTML = '<i class="material-icons">play_arrow</i>';
      }
    });
  </script>
</body>
</html>
