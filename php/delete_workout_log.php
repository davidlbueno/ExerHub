<?php
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $logId = $_POST['log_id'];

  // Delete the workout log and associated log items
  $deleteQuery = "DELETE FROM workout_log_items WHERE workout_log_id = $logId;
                  DELETE FROM workout_logs WHERE id = $logId;";
                  
  $result = multi_query($deleteQuery);

  if ($result) {
    echo 'success';
  } else {
    echo 'error';
  }
}
?>
