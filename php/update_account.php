<?php
session_start();

require_once 'php/db_connect.php';
require_once 'php/db_query.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (isset($_POST['name']) && isset($_POST['userId'])) {
    $name = $_POST['name'];
    $userId = $_POST['userId'];

    // Update the name in the database
    $updateNameQuery = "UPDATE users SET name = ? WHERE id = ?";
    $updateNameParams = array($name, $userId);
    $stmt = query($conn,$updateNameQuery, $updateNameParams);

    if ($stmt) {
      $_SESSION['user_name'] = $name; 
      echo "success";
    } else {
      echo "failed";
    }
  }
}

?>
