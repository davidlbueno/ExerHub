<?php
require_once 'php/header.php';
require_once 'php/db_connect.php';
require_once 'php/db_query.php';

// Check if the form was submitted
if ($_SERVER["REQUEST_METHOD"] === "POST") {
  // Retrieve the form data
  $email = $_POST["email"];
  $password = $_POST["password"];

  // Validate the form data (you can add more validation if needed)
  if (empty($email) || empty($password)) {
    // Handle the case when required fields are missing
    echo "Please enter both email and password.";
    exit;
  }

  // Perform the database operation to authenticate the user
  // Assuming you have a "users" table with columns: id, name, email, password
  $query = "SELECT * FROM users WHERE email = '$email'";

  // Execute the query and check if the user exists
  $result = query($conn, $query);
  if ($result && mysqli_num_rows($result) > 0) {
    $user = mysqli_fetch_assoc($result);
    // Verify the password
    if (password_verify($password, $user['password'])) {
      // Authentication successful, set session variables
      $_SESSION['user_id'] = $user['id'];
      $_SESSION['user_name'] = $user['name'];
      $_SESSION['is_admin'] = $user['is_admin'];

      // Set a session variable for user login status
      $_SESSION['logged_in'] = true;

      header("Location: index.html");
      exit;
    }
  }

  // Handle the case when authentication fails
  echo "Invalid email or password.";
}
?>

<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
  <?php include 'php/header.php'; ?>
  <link rel="stylesheet" href="css/style.css">
  <title>ExerHub - Login</title>
  <!-- Import Material UI scripts -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"></script>

  <script>
    var sessionVars = {
      username: <?php echo isset($_SESSION['user_name']) ? $_SESSION['user_name'] : ''; ?>,
      isAdmin: <?php echo isset($_SESSION['is_admin']) && $_SESSION['is_admin'] ? 'true' : 'false'; ?>
    };
  </script>
</head>
<body class="dark">
  <!-- Navigation bar -->
<nav>
  <div class="nav-wrapper">
    <span class="brand-logo" style="margin-left: 60px"><a href="index.html">ExerHub</a><span class="sub-page-name"><a href="workouts.php">/</a>Login</span></span>
    <a href="index.html" data-target="side-nav" class="show-on-large sidenav-trigger"><i class="material-icons">menu</i></a>
    <ul class="right" id="top-nav"></ul>
  </div>
</nav>
<!-- side navigation bar -->
<ul class="sidenav" id="side-nav"></ul>
<!-- Main content -->
<main>
  <div class="container">
    <h4>Welcome Back</h4>
    <p></p>
    <h5>Log In</h5>
    <form action="login.php" method="POST">
      <div>
        <label for="email">Email:</label>
        <input type="email" id="email" name="email" required>
      </div>
      <div>
        <label for="password">Password:</label>
        <input type="password" id="password" name="password" required>
      </div>
      <button type="submit">Log In</button>
    </form>
  </div>
</main>
<script src="js/nav.js"></script>
<script>document.getElementById("email").focus();</script>
</body>
</html>
