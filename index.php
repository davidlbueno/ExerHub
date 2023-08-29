<?php
include 'php/session.php';
require_once 'php/db_connect.php';
require_once 'php/db_query.php';
$userId = $_SESSION['user_id'];
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <link rel="apple-touch-icon" sizes="180x180" href="assets/img/apple-touch-icon.png">
  <link rel="icon" type="image/png" sizes="32x32" href="assets/img/favicon-32x32.png">
  <link rel="icon" type="image/png" sizes="16x16" href="assets/img/favicon-16x16.png">
  <link rel="manifest" href="/assets/site.webmanifest">
  <link rel="stylesheet" href="css/style.css">
  <title>ExerHub</title>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"></script>
</head>
<body class="dark">
<nav>
  <div class="nav-wrapper">
    <a href="index.html" class="brand-logo" style="margin-left: 60px;">ExerHub</a>
    <a href="index.html" data-target="side-nav" class="show-on-large sidenav-trigger"><i class="material-icons">menu</i></a>
    <ul class="right" id="top-nav"></ul>
    <ul class="sidenav" id="side-nav"></ul>
  </div>
</nav>
<main>
  <?php include 'php/activity_chart.php';?>
  <div class="container">
    <h1>Welcome to ExerHub</h1>
    <p>This web application is designed to help you create and manage bodyweight workout routines. With BWE, you can track your progress, discover new exercises and progressions, and create customized workouts to meet your fitness goals.</p>
    <p>To get started, please log in or create an account.</p>
  </div>
</main>
  <script src="js/nav.js"></script>
  <div class="footer">
    <div class="page-buttons">
      <a href="workouts.php">Workouts</a>
      <a href="exercises.php">Exercises</a>
      <a href="progressions.php">Progressions</a>
    </div>
  </div>
</body>
</html>
