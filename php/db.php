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
    die("An error occurred: " . mysqli_error($conn));
  }
  return $result;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (isset($_POST['query'])) {
    $query = $_POST['query'];
    $params = isset($_POST['params']) ? $_POST['params'] : [];

    $stmt = mysqli_prepare($conn, $query);

    if ($stmt === false) {
      echo json_encode(['error' => 'Failed to prepare statement: ' . mysqli_error($conn)]);
      exit();
    }

    if (!empty($params)) {
      mysqli_stmt_bind_param($stmt, str_repeat('s', count($params)), ...$params);
    }

    $executed = mysqli_stmt_execute($stmt);

    if (!$executed) {
      echo json_encode(['error' => 'SQL Command Failed: ' . mysqli_stmt_error($stmt)]);
      exit();
    }

    $queryType = strtoupper(strtok(trim($query), " "));

    switch($queryType) {
      case 'SELECT':
        $result = mysqli_stmt_get_result($stmt);
        $rows = mysqli_fetch_all($result, MYSQLI_ASSOC);
        echo json_encode($rows);
        break;
      case 'INSERT':
        echo json_encode(['insert_id' => mysqli_insert_id($conn)]);
        break;
      case 'UPDATE':
      case 'DELETE':
      default:
        echo json_encode(['success' => true]);
        break;
    }

    mysqli_stmt_close($stmt);
  }
}
?>
