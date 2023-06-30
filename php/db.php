<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once 'config.php';

// Create connection
$conn = mysqli_connect($host, $user, $password, $database);
// Check connection
if (!$conn) {
  die("Connection failed: " . mysqli_connect_error());
}

function query($query) {
  global $conn;
  $result = mysqli_query($conn, $query);
  if (!$result) {
    error_log("Query failed: " . mysqli_error($conn) . ". Query: " . $query);
    die("An error occurred. Please check the server logs for more information.");
  }
  return $result;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $query = $_POST['query'];
  $params = $_POST['params'];
  // Prepare the statement
  $stmt = mysqli_prepare($conn, $query);
  // Bind the parameters
  mysqli_stmt_bind_param($stmt, str_repeat('s', count($params)), ...$params);
  // Execute the statement
  mysqli_stmt_execute($stmt);
  // Get the newly inserted id
  $id = mysqli_insert_id($conn);
  // Check for errors
  if (mysqli_stmt_errno($stmt)) {
    echo "SQL Command Failed: " . mysqli_stmt_error($stmt);
  } else {
    echo $id;
  }
  // Close the statement
  mysqli_stmt_close($stmt);
}
?>
