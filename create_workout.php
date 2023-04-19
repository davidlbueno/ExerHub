<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
  <meta charset="UTF-8" name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <title>BWE - Create Workout</title>
  <link rel="stylesheet" href="style.css">
  <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"></script>
  <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
  <script src="https://code.jquery.com/ui/1.13.1/jquery-ui.min.js"></script>
  <?php require_once 'db.php'; ?>
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
    <div class="col s12">
    <ol id="items-list" class="sortable"></ol>
    </div>
  </div>
  <div class="row">
    <div class="input-field col s3">
      <select name="item" id="item-select">
        <option value="" disabled selected>Item</option>
        <option value="Push">Push</option>
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
  </div>
  <div class="row">
    <div class="col s12">
      <button id="add-item-btn" class="btn">Add Item</button>
    </div>
  </div>
</main>
<script>
const itemSelect = document.getElementById("item-select");
const exerciseSelect = document.getElementById("exercise-select");
const setsSelect = document.getElementById("sets-select");
const addItemBtn = document.getElementById("add-item-btn");
const itemsList = document.getElementById("items-list");
itemSelect.addEventListener("change", () => {
  const selectedType = itemSelect.value;
  if (selectedType) {
    const xhr = new XMLHttpRequest();
    xhr.open("GET", `get_exercises.php?type=${selectedType}`, true);
    xhr.onload = () => {
      if (xhr.status === 200) {
        const exercises = JSON.parse(xhr.responseText);
        exerciseSelect.innerHTML = "<option value='' disabled selected>Exercise</option>";
        exercises.forEach(exercise => {
          const option = document.createElement("option");
          option.value = exercise.name;
          option.textContent = exercise.name;
          if (selectedType === 'Push' || selectedType === 'Pull' || selectedType === 'Legs') {
            exerciseSelect.appendChild(option);
            exerciseSelect.disabled = false;
            setsSelect.disabled = false;
          } else {
            exerciseSelect.disabled = true;
          }
        });
        if (selectedType === 'Rest') {
          exerciseSelect.disabled = true;
          setsSelect.disabled = true;
        }
      }
    };
    xhr.send();
  } else {
    exerciseSelect.innerHTML = "<option value='' disabled selected>Exercise</option>";
  }
});
document.getElementById("item-select").focus();
addItemBtn.addEventListener("click", () => {
  const itemValue = itemSelect.value;
  const exerciseValue = exerciseSelect.value;
  const secondsInput = document.querySelector('input[name="seconds"]');
  const setsInput = document.querySelector('input[name="sets"]');
  const secondsValue = secondsInput.value;
  const setsValue = setsInput.value;
  const $addItemBtn = $('#add-item-btn');
  if (itemValue === "Rest" && secondsValue) {
    const newItem = document.createElement("li");
    newItem.innerHTML = "Rest";
    newItem.style.backgroundColor = "#454500";
    itemsList.appendChild(newItem);
    itemSelect.value = "";
    secondsInput.value = "";
  } else {
    if (itemValue && exerciseValue && secondsValue && setsValue) {
      const newItem = document.createElement("li");
      newItem.innerHTML = `${itemValue} - ${exerciseValue} (${secondsValue}s, ${setsValue} sets)`;
      const newNumber = itemsList.children.length + 1;
      newItem.setAttribute('value', newNumber);
      itemsList.appendChild(newItem);
      itemSelect.value = "";
      exerciseSelect.value = "";
      secondsInput.value = "";
      setsInput.value = "";
    } else {
      alert("Please enter all required information.");
    }
  }
  $addItemBtn.text('Add Item');
  document.getElementById("item-select").focus();
});
$(function() {
  $( ".sortable" ).sortable({
    revert: true,
    update: function() {
      const itemsList = $(this);
      const items = itemsList.children();
      for (let i = 0; i < items.length; i++) {
        items[i].setAttribute('value', i + 1);
      }
    }
  });
});
$(document).on('click', "#items-list li", function(event) {
  const itemText = this.innerText;
  const itemValue = itemText.split(' ')[0];
  const exerciseValue = itemText.split(' - ')[1].split(' (')[0];
  const itemSelect = document.getElementById("item-select");
  const exerciseSelect = document.getElementById("exercise-select");
  const $addItemBtn = $('#add-item-btn');
  $('.selected').removeClass('selected');
  this.classList.add('selected');
  $addItemBtn.text('Update Item');
  itemSelect.value = itemValue;
  exerciseSelect.value = exerciseValue;
});

$(document).on('click', "#items-list li.selected", function(event) {
  const $addItemBtn = $('#add-item-btn');
  $(this).removeClass('selected');
  $addItemBtn.text('Add Item');
});
</script>
  <?php require_once 'db.php'; ?>
</body>
</body>
</html>
