<?php
session_start();
$userId = $_SESSION['user_id'];
$userName = $_SESSION['user_name'];
$isAdmin = $_SESSION['is_admin'];
$response = [
  'userId' => $userId,
  'userName' => $userName,
  'isAdmin' => $isAdmin
];
echo json_encode($response);
?>
