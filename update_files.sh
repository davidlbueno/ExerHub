#!/bin/bash

# List of PHP files to modify
files=("php/get_workouts.php" "php/save_workout.php" "php/update_workout.php" "php/delete_workout.php" "php/workout_selection.php" "php/get_exercises.php")

# Loop through each file
for file in "${files[@]}"
do
  # Replace 'db.php' with 'db_connect.php'
  sed -i 's/db.php/db_connect.php/g' $file

  # Add line to include 'db_query.php'
  sed -i '/db_connect.php/a require_once '\''db_query.php'\'';' $file
done

# Remove db.php file
rm php/db.php

echo "All done!"

