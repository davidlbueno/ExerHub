<?php
require_once '../../php/db_connect.php';
require_once '../../php/db_query.php';

function update_query($conn, $query, $params) {
  $stmt = mysqli_prepare($conn, $query);
  if ($stmt === false) {
    return ['success' => false, 'error' => mysqli_error($conn)];
  }

  mysqli_stmt_bind_param($stmt, str_repeat('s', count($params)), ...$params);
  $success = mysqli_stmt_execute($stmt);

  if ($success === false) {
    return ['success' => false, 'error' => mysqli_stmt_error($stmt)];
  }

  $affected_rows = mysqli_stmt_affected_rows($stmt);
  return ['success' => true, 'affected_rows' => $affected_rows];
}

$exercise_id = $_POST['exercise_id'];
$exercise_name = $_POST['exercise_name'];
$description = $_POST['description'];
$difficulty = $_POST['difficulty'];
$muscles = $_POST['muscles'];

$conn = db_connect();

$query = 'UPDATE exercises SET exercise_name = ?, description = ?, difficulty = ?, muscles = ? WHERE exercise_id = ?';
$params = [$exercise_name, $description, $difficulty, $muscles, $exercise_id];
$result = update_query($conn, $query, $params);

if ($result['success']) {
    echo "Record updated successfully";
} else {
    echo "Error updating record: " . $result['error'];
}
?>
