<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <link rel="stylesheet" href="style.css">
  <title>BWE - Login</title>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
  <?php require_once 'php/db.php'; ?>
  <?php
  session_start();
  $userId = $_SESSION['user_id'];
  $name = $_SESSION['user_name'];
  $query = "SELECT email FROM users WHERE id = $userId";
  $result = query($query);
  $row = mysqli_fetch_assoc($result);
  $email = $row['email'];
  ?>
</head>
<body class="dark">
<nav>
  <div class="nav-wrapper">
    <span class="brand-logo" style="margin-left: 60px"><a href="index.html">BWE</a><span class="sub-page-name"><a href="workouts.php">/</a>Account</span></span>
    <a href="index.html" data-target="side-nav" class="show-on-large sidenav-trigger"><i class="material-icons">menu</i></a>
    <ul class="right" id="top-nav"></ul>
  </div>
</nav>
<ul class="sidenav" id="side-nav"></ul>
<main>
<div class="container">
  <h4>Account</h4>
  <div>
    <div>
      <label for="name">Name:</label>
      <input type="text" id="name" name="name" value="<?php echo $name ?>" required>
      <button id="name-change-btn" class="btn" disabled="true">Change Name</button>
    </div>
    <div>
      <label for="email">Email:</label>
      <input type="email" id="email" name="email" value="<?php echo $email ?>" required>
      <button id="email-change-btn" class="btn" disabled="true">Change Email</button>
    </div>
  </div>
  <br>
  <button id="change-password-btn" class="btn">Change Password</button>
  <div id="change-password-form" style="display: none;">
    <br>
    <div>
      <label for="current-password">Current Password:</label>
      <input type="password" id="current-password" name="current-password" required>
    </div>
    <div>
      <label for="new-password">New Password:</label>
      <input type="password" id="new-password" name="new-password" required>
    </div>
    <div>
      <label for="confirm-new-password">Confirm New Password:</label>
      <input type="password" id="confirm-new-password" name="confirm-new-password" required>
      <span id="password-error" style="color: red;"></span>
      <span id="password-match" style="color: green;"></span>
      <button id="update-password-btn" class="btn" disabled="true">Update Password</button>
      <button id="cancel-password-btn" class="btn">Cancel</button>
    </div>
</div>
</main>
<script src="js/nav.js"></script>
<script>
  // set change password form to display when button is clicked
  document.getElementById("change-password-btn").addEventListener("click", function() {
    document.getElementById("change-password-form").style.display = "block";
    document.getElementById("change-password-btn").style.display = "none";
  });
  // set change password form to not display when cancel button is clicked
  document.getElementById("cancel-password-btn").addEventListener("click", function() {
    document.getElementById("change-password-form").style.display = "none";
    document.getElementById("change-password-btn").style.display = "block";
    document.getElementById("current-password").value = "";
    document.getElementById("new-password").value = "";
    document.getElementById("confirm-new-password").value = "";
  });
  // check if new password and confirm new password match
  document.getElementById("confirm-new-password").addEventListener("keyup", function() {
    var newPassword = document.getElementById("new-password").value;
    var confirmNewPassword = document.getElementById("confirm-new-password").value;
    // check if new password and confirm new password are greater than 4 characters
    if (newPassword.length > 6 || confirmNewPassword.length > 6) {
      if (newPassword !== confirmNewPassword) {
        document.getElementById("password-error").innerHTML = "Passwords do not match";
        document.getElementById("password-match").innerHTML = "";
      } else {
        document.getElementById("password-error").innerHTML = "";
        document.getElementById("password-match").innerHTML = "Passwords Match";
        // enable the update password submit button
        document.getElementById("update-password-btn").disabled = false;
      }
    } else {
      if (newPassword.length > 1 || confirmNewPassword.length > 1) {
      document.getElementById("password-error").innerHTML = "Password must be at least 6 characters";
      }
    }
  });

  // enable change name button when name input is changed, and disable it again when it is changed back to original value
  document.getElementById("name").addEventListener("keyup", function() {
    var name = document.getElementById("name").value;
    if (name !== "<?php echo $name ?>") {
      document.getElementById("name-change-btn").disabled = false;
    } else {
      document.getElementById("name-change-btn").disabled = true;
    }
  });

  // enable email change button when email is changed and disable it again when it is changed back to original value
  document.getElementById("email").addEventListener("keyup", function() {
    var email = document.getElementById("email").value;
    if (email !== "<?php echo $email ?>") {
      document.getElementById("email-change-btn").disabled = false;
    } else {
      document.getElementById("email-change-btn").disabled = true;
    }
  });

  // create event listener for name change button
  document.getElementById("name-change-btn").addEventListener("click", function() {
    var name = document.getElementById("name").value;
    var userId = "<?php echo $userId ?>";
    $.ajax({
      type: "POST",
      url: "php/update_account.php",
      data: {
        name: name,
        userId: userId
      },
      success: function(data) {
        console.log('"' + data + '"'); // Surround data with quotes to reveal any unexpected leading/trailing spaces or invisible characters.
        if (data === "success") {
          document.getElementById("name-change-btn").disabled = true;
          alert("Name successfully changed");
        } else {
          alert("There was an error updating your name");
        }
      }
    });
  });

</script>
<script>document.getElementById("email").focus();</script>
</body>
</html>
