<?php
session_start();

require_once 'sql.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (isset($_POST['name']) && isset($_POST['userId'])) {
    $name = $_POST['name'];
    $userId = $_POST['userId'];

    // Update the name in the database
    $updateNameQuery = "UPDATE users SET name = ? WHERE id = ?";
    $updateNameParams = array($name, $userId);
    $stmt = query($updateNameQuery, $updateNameParams);

    if ($stmt) {
      $_SESSION['user_name'] = $name; 
      echo "success";
    } else {
      echo "failed";
    }
  }
}

?>
