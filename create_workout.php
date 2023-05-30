<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
  <meta charset="UTF-8" name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <title>BWE - Create Workout</title>
  <link rel="stylesheet" href="style.css">
  <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"></script>
  <script src="https://code.jquery.com/ui/1.13.1/jquery-ui.min.js"></script>
  <?php require_once 'php/db.php'; ?>
</head>
<body class="dark">
  <nav>
    <div class="nav-wrapper">
      <span class="brand-logo" style="margin-left: 60px"><a href="index.html">BWE</a><span class="sub-page-name"><a href="workouts.php">/Workouts/</a>Create</span></span>
      <a href="index.html" data-target="side-nav" class="show-on-large sidenav-trigger"><i class="material-icons">menu</i></a>
      <ul class="right" id="top-nav"></ul>
    </div>
  </nav>
  <ul class="sidenav" id="side-nav"></ul>
  <main class="container">
  <div class="row">
    <div class="row">
      <div class="input-field col s12">
        <input type="text" name="workout-name" id="workout-name" placeholder="Workout Name" style="width:100%;">
      </div>
    </div>
    <div class="col s12">
      <ol id="workout-list" class="sortable"></ol>
    </div>
  </div>
  <div class="row">
    <div class="input-field col s3">
      <select name="type" id="type-select">
        <option value="" disabled selected>Item</option>
        <option value="Push">Push</option>type
        <option value="Pull">Pull</option>
        <option value="Legs">Legs</option>
        <option value="Rest">Rest</option>
      </select>
    </div>
  <div class="input-field col s5">
    <select name="exercise" id="exercise-select" disabled>
      <option value="" disabled selected>Exercise</option>
    </select>
  </div>
  <div class="input-field col s2">
    <input type="number" name="seconds" min="0" max="300" step="15" placeholder="Seconds" style="width:100%;">
  </div>
  <div class="input-field col s2">
    <input type="number" name="sets" id="sets-select" min="0" max="10" step="1" placeholder="Sets" style="width:100%;">
  </div>
  <div class="row">
    <div class="col s12">
      <button id="add-type-btn" class="btn">Add Item</button>
      <button id="clear-list-btn" class="btn">Clear List</button>
      <button id="save-workout-btn" class="btn">Save Workout</button>
    </div> 
  </div>
  </main>
  <script src="js/nav.js"></script>
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      var script = document.createElement('script');
      script.src = 'js/create_workout.js';
      document.head.appendChild(script);
    });
  </script>
  <script src="js/save_workout.js"></script>
  <?php require_once 'php/db.php'; ?>
</body>
</html>
