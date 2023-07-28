<?php
require_once '../../php/db_connect.php';
require_once '../../php/db_query.php';

header('Content-Type: application/json');

function queryExercises($conn) {
    $result = query($conn, 'SELECT e.id AS exercise_id, e.name AS exercise_name, e.type AS exercise_type, e.difficulty, m.name AS muscle_name, em.intensity, ed.description
      FROM exercises e
      JOIN exercise_muscles em ON e.id = em.exercise_id
      JOIN muscles m ON m.id = em.muscle_id
      LEFT JOIN exercise_descriptions ed ON e.id = ed.exercise_id');
    $exercises = array();
    while ($row = mysqli_fetch_assoc($result)) {
        $exerciseId = $row['exercise_id'];
        $exerciseName = $row['exercise_name'];
        $muscleName = $row['muscle_name'];
        $intensity = $row['intensity'];
        $exerciseType = $row['exercise_type'];
        $exerciseDifficulty = $row['difficulty'];
        $exerciseDescription = $row['description'];
        
        if (!isset($exercises[$exerciseName])) {
            $exercises[$exerciseName] = array(
                'exercise_id' => $exerciseId,
                'muscles' => array(),
                'type' => $exerciseType,
                'difficulty' => $exerciseDifficulty,
                'description' => $exerciseDescription
            );
        }
        $exercises[$exerciseName]['muscles'][$muscleName] = $intensity;
    }
    return $exercises;
}

$exercises = queryExercises($conn);
echo json_encode($exercises);
?>
