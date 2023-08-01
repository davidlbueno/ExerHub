<?php
session_start(); // Start the session if not already started

require_once 'php/db_connect.php';
require_once 'php/db_post.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (isset($_POST['newName']) && isset($_POST['userId'])) {
    $new_name = $_POST['newName'];
    $user_id = $_POST['userId'];

    // Update the user's name in the database
    $query = "UPDATE users SET name = ? WHERE id = ?";
    $result = post($conn, $query, [$new_name, $user_id]);

    if ($result['success']) {
      echo "success";
    } else {
      echo "failed";
    }
  }
}
?>
