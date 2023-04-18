<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
  <meta charset="UTF-8" name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <title>BWE - Create Workout</title>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"></script>
  <link rel="stylesheet" href="style.css">
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
  <div id="selection-container"></div>
  <button id="add-selection-btn" class="btn">Add Item</button>
</main>
<script>
  const selectionContainer = document.getElementById("selection-container");
  const addSelectionBtn = document.getElementById("add-selection-btn");
  let rowNumber = 1;
  function createSelectionRow() {
    const selectionRow = document.createElement("div");
    selectionRow.classList.add("row");
    const rowNum = document.createElement("h6");
    rowNum.classList.add("col", "s1");
    rowNum.textContent = rowNumber;
    rowNum.setAttribute("style", "text-align:center"); // Add style attribute
    selectionRow.appendChild(rowNum);
    const itemField = createSelectField("item", "Item", ["Push", "Pull", "Legs", "Rest"], "s3");
    const exerciseField = createSelectField("exercise", "Exercise", ["Exercise 1", "Exercise 2"], "s4");
    const timeField = createNumberField("seconds", "", 0, 300, 15, "Seconds", "s2");
    const setsField = createNumberField("sets", "", 0, 10, 1, "Sets", "s2");
    selectionRow.appendChild(itemField);
    selectionRow.appendChild(exerciseField);
    selectionRow.appendChild(timeField);
    selectionRow.appendChild(setsField);
    rowNumber++;  
    return selectionRow;
  }
  function createSelectField(name, label, options, colClass) {
    const field = document.createElement("div");
    field.classList.add("input-field", "col", colClass);
    const select = document.createElement("select");
    select.name = name;
    const defaultOption = document.createElement("option");
    defaultOption.value = "";
    defaultOption.disabled = true;
    defaultOption.selected = true;
    defaultOption.innerText = label;
    select.appendChild(defaultOption);
    options.forEach(option => {
      const optionElement = document.createElement("option");
      optionElement.value = option.toLowerCase();
      optionElement.innerText = option;
      select.appendChild(optionElement);
    });
    field.appendChild(select);
    return field;
  }
  function createNumberField(name, label, min, max, step, placeholder, colClass) {
    const field = document.createElement("div");
    field.classList.add("input-field", "col", colClass);
    const input = document.createElement("input");
    input.type = "number";
    input.name = name;
    input.min = min;
    input.max = max;
    input.step = step;
    input.placeholder = placeholder;
    const inputLabel = document.createElement("label");
    inputLabel.innerText = label;
    field.appendChild(input);
    field.appendChild(inputLabel);
    return field;
  }
  addSelectionBtn.addEventListener("click", () => {
    const selectionRow = createSelectionRow();
    selectionContainer.appendChild(selectionRow);
  });
</script>
</body>
</html>
