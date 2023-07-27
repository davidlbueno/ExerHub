<?php
function query($conn, $query, $params = []) {
  // Prepare the SQL statement
  $stmt = mysqli_prepare($conn, $query);
  if (!$stmt) {
      error_log("Statement preparation failed: " . mysqli_error($conn));
      die("An error occurred: " . mysqli_error($conn));
  }

  // Bind parameters to the SQL statement
  if (!empty($params)) {
      $types = str_repeat('s', count($params)); // assumes all parameters are strings
      mysqli_stmt_bind_param($stmt, $types, ...$params);
  }

  // Execute the SQL statement
  $result = mysqli_stmt_execute($stmt);
  if (!$result) {
      error_log("Query execution failed: " . mysqli_stmt_error($stmt));
      die("An error occurred: " . mysqli_stmt_error($stmt));
  }

  // For SELECT queries, get the result set
  if (mysqli_stmt_field_count($stmt) > 0) {
      $result = mysqli_stmt_get_result($stmt);
  }

  // Close the statement
  mysqli_stmt_close($stmt);

  return $result;
}
