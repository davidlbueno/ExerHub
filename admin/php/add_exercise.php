<?php
require_once '/php/db_connect.php';
require_once '/php/db_query.php';

$conn = db_connect();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];
    $name = $_POST['name'];
    $type = $_POST['type'];
    $difficulty = $_POST['difficulty'];

    // Input validation
    if (empty($id) || empty($name) || empty($type) || empty($difficulty)) {
        echo json_encode(['error' => 'Missing required fields']);
        exit();
    }

    $query = 'UPDATE exercises SET name = ?, type = ?, difficulty = ? WHERE id = ?';
    $params = [$name, $type, $difficulty, $id];

    $result = db_query($conn, $query, $params);

    if ($result === false) {
        echo json_encode(['error' => 'Database query failed']);
    } else {
        echo json_encode(['success' => 'Exercise updated successfully']);
    }
}
