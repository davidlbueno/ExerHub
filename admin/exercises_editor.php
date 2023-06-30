<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
  <meta charset="UTF-8" name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <title>ExerHub - Admin: Exercises Editor</title>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"></script>
  <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
  <link rel="stylesheet" href="../style.css">
  <?php require_once '../php/db.php'; ?>
</head>
<body class="dark">
<nav>
<div class="nav-wrapper">
  <span class="brand-logo" style="margin-left: 60px"><a href="index.html"><i class="material-icons">home</i>/Admin/</a><span class="sub-page-name">Exercises Editor</span></span>
    <a href="index.html" data-target="side-nav" class="show-on-large sidenav-trigger"><i class="material-icons">menu</i></a>
    <ul class="right" id="top-nav"></ul>
</div>
</nav>
  <ul class="sidenav" id="side-nav"></ul>
  <main class="container"><br>
  <button class="btn" id="pushBtn">Push</button>
  <button class="btn" id="pullBtn">Pull</button>
  <button class="btn" id="legsBtn">Legs</button>
  <div id="main"></div>
</main>
  <script src="../js/nav.js"></script>
  <script>
  document.getElementById("pushBtn").addEventListener("click", () => fetchData('Push'));
  document.getElementById("pullBtn").addEventListener("click", () => fetchData('Pull'));
  document.getElementById("legsBtn").addEventListener("click", () => fetchData('Legs'));

  function fetchData(type) {
    const query = "SELECT id, name, difficulty FROM exercises WHERE type = ? ORDER BY CAST(difficulty AS UNSIGNED) ASC";
    const params = [type];
    $.post('../php/db.php', { query, params }, null, 'json')
      .done((data) => {
        // Display a table for the results
        let table = "<table><thead><tr><th>Name</th><th>Difficulty</th></tr></thead><tbody>";
        data.forEach((exercise, index) => {
          table += "<tr><td>" + exercise.name + "</td><td>" + exercise.difficulty + "</td></tr>";
          table += "<tr id='editRow-" + index + "' style='display: none;'><td colspan='2'>"
                + "<form onsubmit='updateExercise(" + exercise.id + ", event)'>"
                + "<input name='name' type='text' placeholder='Name' value='" + exercise.name + "' required />"
                + "<input name='difficulty' type='number' placeholder='Difficulty' value='" + exercise.difficulty + "' required />"
                + "<button type='submit'>Update</button>"
                + "</form></td></tr>";
        });
        table += "</tbody></table>";
        document.getElementById("main").innerHTML = table;
        // Add event listeners to table rows
        const rows = document.querySelectorAll("table tbody tr:not([id^='editRow-'])");
        rows.forEach((row, index) => {
          row.addEventListener("click", function() {
            const editRow = document.getElementById("editRow-" + index);
            editRow.style.display = (editRow.style.display === 'none' ? '' : 'none');
          });
        });
      })
      .fail((err) => {
        console.log(err);
      });
  }

  function updateExercise(id, event) {
    event.preventDefault();
    const form = event.target;
    const name = form.name.value;
    const type = document.getElementById("pushBtn").classList.contains("active") ? "Push" : document.getElementById("pullBtn").classList.contains("active") ? "Pull" : "Legs";
    const difficulty = form.difficulty.value;
    const query = "UPDATE exercises SET name = ?, difficulty = ? WHERE id = ?";
    const params = [name, difficulty, id];
    $.post('../php/db.php', { query, params })
      .done(() => {
        fetchData(type);  // Refresh the data
      })
      .fail((err) => {
        console.log(err);
      });
  }
</script>
</body>
</html>
