<?php include 'php/header.php';
  require_once 'php/db_connect.php';
  require_once 'php/db_query.php';
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
  <title>ExerHub - Workouts</title>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"></script>
  <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
  <link rel="stylesheet" href="css/style.css">
</head>
<body class="dark">
<nav>
<div class="nav-wrapper">
  <span class="brand-logo" style="margin-left: 60px"><a href="../index.html"><i class="material-icons">home</i>/</a><span class="sub-page-name">Progressions</span></span>
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
</body>
</html>
