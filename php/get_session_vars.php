<?php
session_start();
$userId = $_SESSION{'user_id'};
$userName = $_SESSION['user_name'];
$response = [
  'userId' => $userId,
  'userName' => $userName
];
echo json_encode($response);
?>
