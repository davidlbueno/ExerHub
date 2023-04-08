<!DOCTYPE html>
<html>
<head>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Create a New Workout</title>
  <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
  <link rel="stylesheet" href="https://code.getmdl.io/1.3.0/material.indigo-pink.min.css">
  <script defer src="https://code.getmdl.io/1.3.0/material.min.js"></script>
</head>
<body>
  <div class="mdl-layout mdl-js-layout mdl-layout--fixed-header">
    <header class="mdl-layout__header">
      <div class="mdl-layout__header-row">
        <span class="mdl-layout-title">Create a New Workout</span>
      </div>
    </header>
    <div class="mdl-layout__drawer">
      <span class="mdl-layout-title">Menu</span>
      <nav class="mdl-navigation">
        <a class="mdl-navigation__link" href="#">Link 1</a>
        <a class="mdl-navigation__link" href="#">Link 2</a>
        <a class="mdl-navigation__link" href="#">Link 3</a>
      </nav>
    </div>
    <main class="mdl-layout__content">
      <div class="page-content">
        <h2>Exercise List</h2>
        <ul id="exercise_list" class="mdl-list"></ul>
        <div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label">
          <select class="mdl-textfield__input" id="exercise" name="exercise">
            <option value=""></option>
            <option value="1">Exercise 1</option>
            <option value="2">Exercise 2</option>
            <option value="3">Exercise 3</option>
          </select>
          <label class="mdl-textfield__label" for="exercise">Select an Exercise</label>
        </div>
        <button class="mdl-button mdl-js-button mdl-button--raised mdl-button--colored" onclick="addExerciseToList()">Add Exercise</button>
      </div>
    </main>
  </div>

  <script>
    function addExerciseToList() {
      var exerciseSelect = document.getElementById("exercise");
      var exerciseList = document.getElementById("exercise_list");
      var selectedExercise = exerciseSelect.options[exerciseSelect.selectedIndex];
      var exerciseId = selectedExercise.value;
      var exerciseName = selectedExercise.text;
      var listItem = document.createElement("li");
      listItem.classList.add("mdl-list__item");
      listItem.innerHTML = '<span class="mdl-list__item-primary-content">' + exerciseName + '</span><input type="hidden" name="exercise_ids[]" value="' + exerciseId + '">';
      exerciseList.appendChild(listItem);
    }
  </script>
  <?php
  require_once('db.php');
  $exercises = fetch_data("SELECT id, CONCAT(name, ' (', difficulty, ')') as display_name FROM exercises ORDER BY name ASC");
  ?>
<form>
  <ul id="selected_items"></ul>
  <label for="add_item">Add item:</label>
  <select id="add_item">
    <option value="">-- Select an option --</option>
    <option value="rest">Rest</option>
    <option value="exercise">Exercise</option>
  </select>
  <button type="button" onclick="addItem()">Add Item</button>
  <script>
    function addItem() {
      var addItemSelect = document.getElementById("add_item");
      var addItemValue = addItemSelect.options[addItemSelect.selectedIndex].value;
      if (addItemValue === 'rest') {
        var restDurationInput = '<input type="number" name="rest_duration" placeholder="Enter rest duration (seconds)" required/>';
        var li = document.createElement("li");
        li.innerHTML = 'Rest ' + restDurationInput;
        document.getElementById("selected_items").appendChild(li);
      } else if (addItemValue === 'exercise') {
        var exerciseSelect = '<select name="exercise"><option value="">-- Select an exercise --</option><?php foreach ($exercises as $exercise) {echo '<option value="' . $exercise['id'] . '">' . $exercise['display_name'] . '</option>';}?></select>';
        var repsOrTimeInput = '<input type="number" name="reps_or_time_input" placeholder="Enter reps" required/>';
        var toggleButton = '<button type="button" id="toggle-button" onclick="toggleRepsOrTime()">Time</button>';
        var li = document.createElement("li");
        li.innerHTML = exerciseSelect + ' ' + toggleButton + ' ' + repsOrTimeInput;
        document.getElementById("selected_items").appendChild(li);
      }
    }
    function toggleRepsOrTime() {
      var toggleButton = document.getElementById("toggle-button");
      var repsOrTimeInput = document.getElementsByName("reps_or_time_input")[0];
      if (toggleButton.innerHTML === 'Time') {
        toggleButton.innerHTML = 'Reps';
        repsOrTimeInput.setAttribute('type', 'text');
        repsOrTimeInput.setAttribute('placeholder', 'Enter time (format: mm:ss)');
      } else if (toggleButton.innerHTML === 'Reps') {
        toggleButton.innerHTML = 'Time';
        repsOrTimeInput.setAttribute('type', 'number');
        repsOrTimeInput.setAttribute('placeholder', 'Enter reps');
      }
    }
  </script>
</form>
  </script>
  <button type="submit" class="button">Save Workout</button>
</form>
</body>
</html>
