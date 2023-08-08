<?php
$pageTitle = "ExerHub - Select Workouts";
include 'php/session.php';
require_once 'php/db_connect.php';
require_once 'php/db_query.php';
if (!isset($_SESSION['user_id'])) {
  header('Location: login.php');
  exit;
}
require_once 'php/header.php';

$user_id = $_SESSION['user_id'];

// Fetch all public workouts
$public_workouts = query($conn, "SELECT * FROM workouts WHERE is_public = 1");

// Fetch all workouts created by the current user
$user_workouts = query($conn, "SELECT * FROM workouts WHERE user_id = $user_id");

// Fetch all selected workouts for the current user
$selected_workouts_result = query($conn, "SELECT workout_id FROM user_selected_workouts WHERE user_id = $user_id");
$selected_workouts = array();
while ($row = mysqli_fetch_assoc($selected_workouts_result)) {
    $selected_workouts[] = $row['workout_id'];
}

function display_workouts($workouts, $selected_workouts) {
  while ($workout = mysqli_fetch_assoc($workouts)) {
      $checked = in_array($workout['id'], $selected_workouts) ? 'checked' : '';
      echo "<li>
              <label>
                  <input type='checkbox' class='workout-checkbox filled-in' data-workout-id='{$workout['id']}' $checked />
                  <span style='color: #fff;'>{$workout['name']}</span>
              </label>
            </li>";
  }
}
?>

<body class="dark">
<nav>
<div class="nav-wrapper">
    <span class="brand-logo" style="margin-left: 60px"><a href="index.html"><i class="material-icons">home</i>/</a><a href="workouts.php">Workouts</a>/<span class="sub-page-name">Select Workouts</span></span>
    <a href="index.html" data-target="side-nav" class="show-on-large sidenav-trigger"><i class="material-icons">menu</i></a>
    <ul class="right" id="top-nav"></ul>
</div>
</nav>
  <ul class="sidenav" id="side-nav"></ul>
  <main class="container">
    <h6>Public Workouts</h6>
    <label>
        <input type="checkbox" class="filled-in select-all" data-target="public-workouts" />
        <span>Select All</span>
    </label>
    <ul class="public-workouts">
        <?php display_workouts($public_workouts, $selected_workouts); ?>
    </ul>
    <br>
    <h6>Your Workouts</h6>
    <label>
        <input type="checkbox" class="filled-in select-all" data-target="user-workouts" />
        <span>Select All</span>
    </label>
    <ul class="user-workouts">
        <?php display_workouts($user_workouts, $selected_workouts); ?>
    </ul>
  </main>
  <script src="js/nav.js"></script>
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
  <script>
  $(document).ready(function() {
      $('.workout-checkbox').change(function() {
          var workout_id = $(this).data('workout-id');
          var selected = $(this).is(':checked');
          $.post('php/workout_selection.php', { workout_id: workout_id, selected: selected });
      });
      
      $('.select-all').change(function() {
          var selected = $(this).is(':checked');
          var target = $(this).data('target');
          $('.' + target).find('.workout-checkbox').prop('checked', selected).change();
      });
  });
  </script>
</body>
</html>
