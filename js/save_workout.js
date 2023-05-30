saveWorkoutBtn = document.getElementById("save-workout-btn");
saveWorkoutBtn.addEventListener("click", () => {
  const workoutName = document.getElementById("workout-name").value;
  const workoutList = document.getElementById("workout-list");
  const types = workoutList.children;

  // Create an array to store the exercise data
  const workoutData = [];

  for (let i = 0; i < types.length; i++) {
    const typeText = types[i].textContent;
    const typeValue = typeText.split(' ')[0];
    const exerciseValue = typeText.split(' - ')[1].split(' (')[0];
    const secondsText = typeText.match(/\((\d+)s\)/)[1];
    const secondsValue = parseInt(secondsText, 10);

    workoutData.push({
      type: typeValue,
      exercise: exerciseValue,
      seconds: secondsValue,
    });
  }

  // Send an AJAX request to the PHP script
  const xhr = new XMLHttpRequest();
  xhr.open("POST", "php/save_workout.php", true);
  xhr.setRequestHeader("Content-Type", "application/json");
  xhr.onload = () => {
    if (xhr.status === 200) {
      // Handle the response from the PHP script if needed
      console.log(xhr.responseText);
    } else {
      // Handle errors
      console.error(xhr.responseText);
    }
  };

  const payload = {
    workoutName: workoutName, // Correct property name
    workoutData: workoutData, // Correct property name
  };

  xhr.send(JSON.stringify(payload));
});
