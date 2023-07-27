<?php
require_once 'db_connect.php';
require_once 'db_query.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['exercise_id'])) {
        $exerciseId = $_POST['exercise_id'];
        $query = 'SELECT description FROM exercise_descriptions WHERE exercise_id = ?';
        $params = [$exerciseId];
        $result = query($conn, $query, $params);
        echo json_encode($result);
    } else {
        http_response_code(400);
        echo json_encode(['error' => 'Missing exercise_id parameter']);
    }
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Invalid request method']);
}
?>
