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

  $stmt = mysqli_prepare($conn, $query);
  
  mysqli_stmt_bind_param($stmt, str_repeat('s', count($params)), ...$params);
  
  mysqli_stmt_execute($stmt);

  if (isset($_POST['update_session'])) {
    session_start();
    $_SESSION['user_name'] = $params[0];
  }

  if (mysqli_stmt_errno($stmt)) {
    echo "SQL Command Failed: " . mysqli_stmt_error($stmt);
  } else {
    $queryType = strtoupper(strtok(trim($query), " "));

    if ($queryType === 'INSERT') {
      echo mysqli_insert_id($conn);
    } else if ($queryType === 'UPDATE' || $queryType === 'DELETE') {
      echo "success";
    } else {
      echo "success";
    }
  }
  mysqli_stmt_close($stmt);
}
?>
