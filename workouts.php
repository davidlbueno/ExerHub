<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
  <meta charset="UTF-8" name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <title>BWE - Workouts</title>
  <script type="text/javascript" src="//code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"></script>
  <link rel="stylesheet" href="style.css">
  <?php require_once 'db.php'; ?>
</head>
<body class="dark">
  <nav>
    <div class="nav-wrapper">
      <a href="index.html" class="brand-logo">BWE</a>
      <a href="index.html" data-target="side-nav" class="show-on-large sidenav-trigger"><i class="material-icons">menu</i></a>
      <ul class="right" id="top-nav"></ul>
    </div>
  </nav>
  <ul class="sidenav" id="side-nav"></ul>
  <main class="container">
    <h2>Workouts</h2>
  </main>
  <script src="nav.js"></script>
</body>
</html>
