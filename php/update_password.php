<?php
session_start(); // Start the session if not already started

require_once 'php/db_connect.php';
require_once 'php/db_post.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (isset($_POST['currentPassword']) && isset($_POST['newPassword']) && isset($_POST['userId'])) {
    $currentPassword = $_POST['currentPassword'];
    $newPassword = $_POST['newPassword'];
    $userId = $_POST['userId'];

    // Get the current password from the database
    $getPasswordQuery = "SELECT password FROM users WHERE id = ?";
    $result = post($conn, $getPasswordQuery, [$userId]);
    $row = $result[0];
    $password = $row['password'];

    // Check if the current password matches the password in the database
    if (password_verify($currentPassword, $password)) {
      // Update the password in the database
      $updatePasswordQuery = "UPDATE users SET password = ? WHERE id = ?";
      $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
      $result = post($conn, $updatePasswordQuery, [$hashedPassword, $userId]);

      if ($result['success']) {
        echo "success";
      } else {
        echo "failed";
      }
    } else {
      echo "Current password is incorrect";
    }
  }
}
?>
