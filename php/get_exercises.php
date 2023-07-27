<?php
require_once 'db_connect.php';
require_once 'db_query.php';

$type = $_GET['type'];

$result = query("SELECT name FROM exercises WHERE type='$type'");
$exercises = [];
while ($row = mysqli_fetch_assoc($result)) {
  $exercises[] = [
    'name' => $row['name']
  ];
}

header('Content-Type: application/json');
echo json_encode($exercises);
?>
