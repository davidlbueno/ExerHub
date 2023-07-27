<?php
function query($conn, $query, $params = []) {
  // Prepare the SQL statement
  $stmt = mysqli_prepare($conn, $query);
  if (!$stmt) {
    return ['success' => false, 'error' => "Statement preparation failed: " . mysqli_error($conn)];
  }

  // Bind parameters to the SQL statement
  if (!empty($params)) {
    $types = str_repeat('s', count($params)); // assumes all parameters are strings
    mysqli_stmt_bind_param($stmt, $types, ...$params);
  }

  // Execute the SQL statement
  $result = mysqli_stmt_execute($stmt);
  if (!$result) {
    return ['success' => false, 'error' => "Query execution failed: " . mysqli_stmt_error($stmt)];
  }

  // For INSERT queries, get the last insert id
  if (mysqli_stmt_field_count($stmt) === 0) {
    $result = ['success' => true, 'insert_id' => mysqli_stmt_insert_id($stmt)];
  }

  // For SELECT queries, get the result set
  if (mysqli_stmt_field_count($stmt) > 0) {
    $result = mysqli_stmt_get_result($stmt);
  }

  // Close the statement
  mysqli_stmt_close($stmt);

  return $result;
}
