<?php
session_start();

// Unset all session variables
$_SESSION = array();

// Destroy the session
session_destroy();

// Redirect the user to the desired page after logout
header('Location: index.html'); // Replace 'index.php' with the appropriate destination

// Make sure to exit the script to prevent further execution
exit;
?>
