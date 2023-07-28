<?php
require_once 'db_connect.php';
require_once 'db_query.php';

function post($conn, $query, $params) {
    $stmt = mysqli_prepare($conn, $query);

    if ($stmt === false) {
      http_response_code(500);
      echo json_encode(['error' => 'Failed to prepare statement: ' . mysqli_error($conn)]);
      exit();
    }

    if (!empty($params)) {
      mysqli_stmt_bind_param($stmt, str_repeat('s', count($params)), ...$params);
    }

    $executed = mysqli_stmt_execute($stmt);

    if (!$executed) {
      http_response_code(500);
      echo json_encode(['error' => 'SQL Command Failed: ' . mysqli_stmt_error($stmt)]);
      exit();
    }

    $queryType = strtoupper(strtok(trim($query), " "));

    switch($queryType) {
      case 'SELECT':
        $result = mysqli_stmt_get_result($stmt);
        $rows = mysqli_fetch_all($result, MYSQLI_ASSOC);
        return $rows;
      case 'INSERT':
        return ['insert_id' => mysqli_insert_id($conn)];
      case 'UPDATE':
      case 'DELETE':
      default:
        return ['success' => true];
    }

    mysqli_stmt_close($stmt);
}
