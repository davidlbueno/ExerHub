<?php
session_start();
$userName = $_SESSION['user_name'];
$sessionData = $_SESSION;
$response = [
  'sessionData' => $sessionData,
  'userName' => $userName
];
echo json_encode($response);
?>
