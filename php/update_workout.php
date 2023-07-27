<?php
require_once 'db_connect.php';
require_once 'db_query.php';
require_once 'db_post.php';

$data = json_decode(file_get_contents("php://input"), true);
$workoutId = $data['workoutId'];
$workoutName = $data['workoutName'];
$workoutData = $data['workoutData'];
$isPublic = $data['isPublic'];

$response = array();

try {
  // Update workout name and public status
  $query = "UPDATE workouts SET name = ?, is_public = ? WHERE id = ?";
  post($conn, $query, [$workoutName, $isPublic, $workoutId]);
  $response['message'][] = "Workout name updated successfully!";

  // Delete existing workout sequence items
  $query = "DELETE FROM workout_sequences WHERE workout_id = ?";
  post($conn, $query, [$workoutId]);
  $response['message'][] = "Deleted existing workout sequence items!";

  // Insert updated workout sequence items
  $errorEncountered = false;
  foreach ($workoutData as $item) {
    $type = $item['type'];
    $seconds = $item['seconds'];
    $warmup = $item['warmup'];

    if ($type === 'Rest') {
      $exerciseId = 'NULL';
    } else {
      // Retrieve exercise ID
      $query = "SELECT id FROM exercises WHERE name = ?";
      $result = query($conn, $query, [$item['exercise']]);
      $row = mysqli_fetch_assoc($result);
      $exerciseId = $row['id'];

      if (!$exerciseId) {
        throw new Exception("Exercise '{$item['exercise']}' not found!");
      }
    }
    
    // Insert workout sequence item
    $query = "INSERT INTO workout_sequences (workout_id, type, exercise_id, seconds, warmup) VALUES (?, ?, ?, ?, ?)";
    post($conn, $query, [$workoutId, $type, $exerciseId, $seconds, $warmup]);
  }

  $response['success'] = true;
  $response['message'][] = "Workout saved successfully!";
} catch (Exception $e) {
  $response['success'] = false;
  $response['error'] = $e->getMessage();
}

echo json_encode($response);
?>
