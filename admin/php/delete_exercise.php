<?php
require_once '/php/db_connect.php';
require_once '/php/db_query.php';

$conn = db_connect();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];

    // Input validation
    if (empty($id)) {
        echo json_encode(['error' => 'Missing required fields']);
        exit();
    }

    $query = 'DELETE FROM exercises WHERE id = ?';
    $params = [$id];

    $result = db_query($conn, $query, $params);

    if ($result === false) {
        echo json_encode(['error' => 'Database query failed']);
    } else {
        echo json_encode(['success' => 'Exercise deleted successfully']);
    }
}
