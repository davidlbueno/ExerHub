<?php
session_start();

//$isLoggedIn = isset($_SESSION['user_id']) && isset($_SESSION['user_name']);
$userName = $_SESSION['user_name'];

// Return the user status as JSON
//echo json_encode(['isLoggedIn' => $isLoggedIn]);
echo json_encode(['userName' => $userName]);
?>
