# ExerHub

## Navigation Map of ExerHub

### Entry Points
- `index.php`: Main entry point for the application.

### PHP Files
- `session.php`: Manages user sessions.
- `db_connect.php`: Connects to the database.
- `db_query.php`: Contains database queries.
- `header.php`: Includes the header for the application.
- `activity_chart.php`: Generates the activity chart.

#### Account Management
- `register.php`: Handles user registration.
- `logout.php`: Logs out the user.
- `update_password.php`: Updates the user's password.
- `update_name.php`: Updates the user's name.
- `update_account.php`: Updates the user account.

#### Workout Management
- `workout_selection.php`: Handles workout selection.
- `save_workout.php`: Saves a new workout.
- `delete_workout.php`: Deletes a workout.
- `update_workout.php`: Updates an existing workout.
- `get_workouts.php`: Fetches workouts.

#### Exercise Management
- `get_exercises.php`: Fetches exercises.
- `get_exercise_description.php`: Fetches exercise descriptions.
- `select_exercise_modal.php`: Manages the exercise selection modal.

#### Log Management
- `create_log_items.php`: Creates log items.
- `delete_workout_log.php`: Deletes a workout log.
- `update_log.php`: Updates a workout log.

#### AWS and Session
- `get_aws_creds.php`: Fetches AWS credentials.
- `get_session_vars.php`: Fetches session variables.

#### Miscellaneous
- `db_post.php`: Handles database POST requests.

### HTML Files
- `nav.html`: Contains the navigation bar.
- `footer.html`: Contains the footer.

### JavaScript Files
- `nav.js`: Manages navigation items.
- `utils.js`: Contains utility functions.
- `workout_player.js`: Manages the workout player.
- `create_workout.js`: Manages workout creation.
- `workout_graph.js`: Manages the workout graph.
- `footer.js`: Manages the footer.
- `save_workout.js`: Manages saving workouts.
- `update_workout.js`: Manages updating workouts.
- `edit_workout_log.js`: Manages editing workout logs.

### Admin Section
- `index.html`: Admin dashboard entry point.
- `exercises_editor.php`: Manages exercise editing in the admin dashboard.
- `progressions_editor.php`: Manages progression editing in the admin dashboard.
