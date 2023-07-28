<?php
require_once '../../php/db_connect.php';
require_once '../../php/db_query.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['exercise_id'])) {
        $exerciseId = $_POST['exercise_id'];
        $query = 'SELECT description FROM exercise_descriptions WHERE exercise_id = ?';
        $params = [$exerciseId];
        $result = query($conn, $query, $params);

        // Fetch the result as an associative array
        $data = mysqli_fetch_assoc($result);

        echo json_encode($data['description']);
    } else {
        http_response_code(400);
        echo json_encode(['error' => 'Missing exercise_id parameter']);
    }
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Invalid request method']);
}
?>
