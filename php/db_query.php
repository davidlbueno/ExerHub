<?php
function query($conn, $query) {
  $result = mysqli_query($conn, $query);
  if (!$result) {
    error_log("Query failed: " . mysqli_error($conn) . ". Query: " . $query);
    die("An error occurred: " . mysqli_error($conn));
  }
  return $result;
}
