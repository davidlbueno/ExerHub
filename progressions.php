<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
  <meta charset="UTF-8" name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <title>ExerHub - Workouts</title>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"></script>
  <link rel="stylesheet" href="style.css">
  <?php require_once 'php/db.php'; ?>
</head>
<body class="dark">
<nav>
<div class="nav-wrapper">
  <span class="brand-logo" style="margin-left: 60px"><a href="index.html"><i class="material-icons">home</i>/</a><span class="sub-page-name">Progressions</span></span>
    <a href="index.html" data-target="side-nav" class="show-on-large sidenav-trigger"><i class="material-icons">menu</i></a>
    <ul class="right" id="top-nav"></ul>
</div>
</nav>
  <ul class="sidenav" id="side-nav"></ul>
  <main class="container"><br>
  <button class="btn" id="pushBtn">Push</button>
  <button class="btn" id="pullBtn">Pull</button>
  <button class="btn" id="legsBtn">Legs</button>
  


</main>
  <script src="js/nav.js"></script>
  <script>
  document.getElementById("pushBtn").addEventListener("click", push);
  document.getElementById("pullBtn").addEventListener("click", pull);
  document.getElementById("legsBtn").addEventListener("click", legs);
  function push(){
    
    alert("push");
  }
  function pull(){
    alert("pull");
  }
  function legs(){
    alert("legs");
  }
  </script>

</body>
</html>
