<?php
require_once '/php/db_connect.php';
require_once '/php/db_query.php';

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

$sql = "UPDATE exercises SET exercise_name='$exercise_name', description='$description', difficulty='$difficulty', muscles='$muscles' WHERE exercise_id='$exercise_id'";

if ($conn->query($sql) === TRUE) {
    echo "Record updated successfully";
} else {
    echo "Error updating record: " . $conn->error;
}

