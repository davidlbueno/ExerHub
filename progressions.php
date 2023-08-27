<?php
$pageTitle = "Progressions";
include 'php/session.php';
require_once 'php/header.php';
require_once 'php/db_connect.php';
require_once 'php/db_query.php';
?>

<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>

<body class="dark">
<?php include 'html/nav.html'; ?>
  <main class="container"><br>
  <button class="btn" id="pushBtn">Push</button>
  <button class="btn" id="pullBtn">Pull</button>
  <button class="btn" id="legsBtn">Legs</button>
  <div id="main"></div>
</main>
  <script src="js/nav.js"></script>
  <script>
  document.getElementById("pushBtn").addEventListener("click", () => fetchData('Push'));
  document.getElementById("pullBtn").addEventListener("click", () => fetchData('Pull'));
  document.getElementById("legsBtn").addEventListener("click", () => fetchData('Legs'));
  function fetchData(type) {
  $.get('php/get_exercises.php', { type, includeDifficulty: true }, null, 'json')
    .done((data) => {
      // Display a table for the results
      let table = "<table><thead><tr><th>Name</th><th>Difficulty</th></tr></thead><tbody>";
      data.forEach((exercise) => {
        table += "<tr><td>" + exercise.name + "</td><td>" + exercise.difficulty + "</td></tr>";
      });
      table += "</tbody></table>";
      document.getElementById("main").innerHTML = table;
    })
    .fail((err) => {
      console.log(err);
    });
  }
</script>
<?php include 'html/footer.html'; ?>
</body>
</html>
