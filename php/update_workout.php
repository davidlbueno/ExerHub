<?php
include 'db.php';

$data = json_decode(file_get_contents("php://input"), true);
$workoutId = $data['workoutId'];
$workoutName = $data['workoutName'];
$workoutData = $data['workoutData'];

$response = array();

try {
  // Update workout name
  $query = "UPDATE workouts SET name = '$workoutName' WHERE id = $workoutId"; // Use workoutId in the query
  $queryResult = query($query);
  if (!$queryResult) {
    throw new Exception("Failed to update workout name: " . mysqli_error($conn));
  }
  $response['message'][] = "Workout name updated successfully!";

  // Delete existing workout sequence items
  $query = "DELETE FROM workout_sequences WHERE workout_id = $workoutId";
  $queryResult = query($query);
  if (!$queryResult) {
    throw new Exception("Failed to delete existing workout sequence items: " . mysqli_error($conn));
  }
  $response['message'][] = "Deleted existing workout sequence items!";

  // Insert updated workout sequence items
  $errorEncountered = false;
  foreach ($workoutData as $item) { // Update variable name
    $type = $item['type'];
    $exercise = $item['exercise'];
    $seconds = $item['seconds'];
    $warmup = $item['warmup'];

    if ($type === 'Rest') {
      $exerciseId = 'NULL';
    } else {
      // Retrieve exercise ID
      $query = "SELECT id FROM exercises WHERE name = '$exercise'";
      $queryResult = query($query);
      if (!$queryResult) {
        throw new Exception("Failed to retrieve exercise ID: " . mysqli_error($conn));
      }
      $row = mysqli_fetch_assoc($queryResult);
      $exerciseId = $row['id'];

      if (!$exerciseId) {
        throw new Exception("Exercise '$exercise' not found!");
      }
    }
    
    // Insert workout sequence item
    $query = "INSERT INTO workout_sequences (workout_id, type, exercise_id, seconds, warmup) VALUES ($workoutId, '$type', $exerciseId, $seconds, $warmup)";
    $queryResult = query($query);
    if (!$queryResult) {
      throw new Exception("Failed to insert workout sequence item: " . mysqli_error($conn));
    }
  }

  $response['success'] = true;
  $response['message'][] = "Workout saved successfully!";
} catch (Exception $e) {
  $response['success'] = false;
  $response['error'] = $e->getMessage();
}

echo json_encode($response);
?>
