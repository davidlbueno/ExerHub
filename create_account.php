<?php include 'php/header.php';
  require_once 'php/db.php';
?>
  <title>ExerHub - Create Account</title>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"></script>
  <link rel="stylesheet" href="css/style.css">
</head>
<body class="dark">
<nav>
  <div class="nav-wrapper">
    <span class="brand-logo" style="margin-left: 60px"><a href="index.html"><i class="material-icons">home</i>/</a><span class="sub-page-name">Create Account</span></span>
    <a href="index.html" data-target="side-nav" class="show-on-large sidenav-trigger"><i class="material-icons">menu</i></a>
    <ul class="right" id="top-nav"></ul>
  </div>
</nav>
<ul class="sidenav" id="side-nav"></ul>
<main class="container">
  <div class="row">
    <div class="col s8">
      <h4>Create Account</h4>
      <form action="php/register.php" method="post">
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
<script>document.getElementById("name").focus();</script>
<script src="js/nav.js"></script>
</body>
</html>
