<?php require_once 'php/header.php';
  require_once 'php/db_connect.php';
  require_once 'php/db_query.php';
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
  <link rel="stylesheet" href="css/style.css">
  <title>ExerHub - Login</title>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
  <?php
  $userId = $_SESSION['user_id'];
  $query = "SELECT name, email FROM users WHERE id = $userId";
  $result = query($conn, $query);
  $row = mysqli_fetch_assoc($result);
  $name = $row['name'];
  $email = $row['email'];
  $_SESSION['user_name'] = $name;
  $_SESSION['user_email'] = $email;
  
  ?>
</head>
<body class="dark">
<nav>
  <div class="nav-wrapper">
    <span class="brand-logo" style="margin-left: 60px"><a href="index.html"><i class="material-icons">home</i></a><span class="sub-page-name"><a href="workouts.php">/</a>Account</span></span>
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
      <span id="incorrect-password" style="color: red;"></span>
    </div>
    <div>
      <label for="new-password">New Password:</label>
      <input type="password" id="new-password" name="new-password" required>
    </div>
    <div>
      <label for="confirm-new-password">Confirm New Password:</label>
      <input type="password" id="confirm-new-password" name="confirm-new-password" required>
      <span id="password-error" style="color: red;"></span>
      <span id="password-match" style="color: green;"></span><br>
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
    var postData = {
      name: name
    };
    $.post("php/update_name.php", postData, function(data) {
      if (data.trim() === 'success') {
          alert("Name successfully updated");
          window.location.reload();
      } else {
          alert("Name update failed");
      }
    });
  });

  // create event listener for password change button
  document.getElementById("update-password-btn").addEventListener("click", function() {
  var currentPassword = document.getElementById("current-password").value;
  var newPassword = document.getElementById("new-password").value;
  var userId = "<?php echo $userId ?>";
  var params = {currentPassword: currentPassword, newPassword: newPassword, userId: userId};
    
    $.post("php/update_password.php", params, function(data) {
      if (data === "success") {
        alert("Password successfully updated");
        window.location.reload();
      } else if (data === "Current password is incorrect") {
        // Reset form fields
        document.getElementById("current-password").value = "";
        document.getElementById("new-password").value = "";
        document.getElementById("confirm-new-password").value = "";
        // Display error message
        document.getElementById("incorrect-password").innerHTML = "Incorrect password";
      } else {
        alert("Password update failed: " + data);
      }
    });
  });
</script>
<script>document.getElementById("email").focus();</script>
</body>
</html>
