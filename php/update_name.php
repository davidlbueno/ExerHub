<?php
require_once 'db_connect.php';
require_once 'db_query.php';

session_start();

if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    exit;
}

$user_id = $_SESSION['user_id'];
$new_name = $_POST['name'];

$query = "UPDATE users SET name = ? WHERE id = ?";
post($conn, $query, [$new_name, $user_id]);

echo 'success';
