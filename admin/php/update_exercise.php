<?php
require_once '../../php/db_connect.php';
require_once '../../php/db_query.php';

$conn = db_connect();

if (!isset($_POST['exercise_id'], $_POST['exercise_name'], $_POST['description'], $_POST['difficulty'], $_POST['muscles'])) {
    echo "Error: Not all form data was sent.";
    exit;
}

$exercise_id = $_POST['exercise_id'];
$exercise_name = $_POST['exercise_name'];
$description = $_POST['description'];
$difficulty = $_POST['difficulty'];
$muscles = $_POST['muscles'];

$query = 'UPDATE exercises SET exercise_name = ?, description = ?, difficulty = ?, muscles = ? WHERE exercise_id = ?';
$params = [$exercise_name, $description, $difficulty, $muscles, $exercise_id];
$result = query($conn, $query, $params);

if ($result['success']) {
    echo "Record updated successfully";
} else {
    echo "Error updating record: " . $result['error'];
}
