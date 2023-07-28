<?php
require_once '../../php/db_connect.php';

$exerciseId = $_POST['exerciseId'];

try {
    $stmt = $pdo->prepare('SELECT description FROM exercise_descriptions WHERE exercise_id = ?');
    $stmt->execute([$exerciseId]);
    $description = $stmt->fetch();

    echo json_encode($description);
} catch (PDOException $e) {
    echo 'Error: ' . $e->getMessage();
}
?>
