<?php
require_once 'php/db_connect.php';
require_once 'php/db_query.php';
require_once 'php/db_post.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (isset($_POST['currentPassword']) && isset($_POST['newPassword']) && isset($_POST['userId'])) {
    $currentPassword = $_POST['currentPassword'];
    $newPassword = $_POST['newPassword'];
    $userId = $_POST['userId'];

    // Get the current password from the database
    $getPasswordQuery = "SELECT password FROM users WHERE id = ?";
    $stmt = mysqli_prepare($conn, $getPasswordQuery);
    mysqli_stmt_bind_param($stmt, 's', $userId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    $password = $row['password'];


    // Check if the current password matches the password in the database
    if (password_verify($currentPassword, $password)) {
      // Update the password in the database
      $updatePasswordQuery = "UPDATE users SET password = ? WHERE id = ?";
      $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
      $stmt = mysqli_prepare($conn, $updatePasswordQuery);
      mysqli_stmt_bind_param($stmt, 'ss', $hashedPassword, $userId);
      $result = mysqli_stmt_execute($stmt);


      if ($stmt) {
        echo "success";
      } else {
        echo "failed";
      }
    } else {
      echo "Current password is incorrect";
    }
  }
}