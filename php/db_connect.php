<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once '../../config.php';

// Create connection
$conn = mysqli_connect($host, $user, $password, $database);

// Check connection
if (!$conn) {
  die("Connection failed: " . mysqli_connect_error());
}
