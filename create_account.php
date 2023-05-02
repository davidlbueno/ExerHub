<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
  <meta charset="UTF-8" name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <title>BWE - Create Account</title>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"></script>
  <link rel="stylesheet" href="style.css">
  <?php require_once 'db.php'; ?>
</head>
<body class="dark">
<nav>
  <div class="nav-wrapper">
    <span class="brand-logo" style="margin-left: 60px"><a href="index.html">BWE/</a><span class="sub-page-name">Create Account</span></span>
    <a href="index.html" data-target="side-nav" class="show-on-large sidenav-trigger"><i class="material-icons">menu</i></a>
    <ul class="right" id="top-nav"></ul>
  </div>
</nav>

<ul class="sidenav" id="side-nav"></ul>

<main class="container">
  <div class="row">
    <div class="col s8">
      <h3>Create Account</h3>
      <form action="register.php" method="post">
        <div class="input-field">
          <input type="text" id="name" name="name" required>
          <label for="name">Name</label>
        </div>
        <div class="input-field">
          <input type="email" id="email" name="email" required>
          <label for="email">Email</label>
        </div>
        <div class="input-field">
          <input type="password" id="password" name="password" required>
          <label for="password">Password</label>
        </div>
        <button class="btn waves-effect waves-light" type="submit">Create Account</button>
      </form>
    </div>
  </div>
</main>

<script src="nav.js"></script>
</body>
</html>
