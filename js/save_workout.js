saveWorkoutBtn = document.getElementById("save-workout-btn");
saveWorkoutBtn.addEventListener("click", () => {
  const workoutName = document.getElementById("workout-name").value;
  const typesList = document.getElementById("types-list");
  const types = typesList.children;

  // Create an array to store the exercise data
  const workoutData = [];

  for (let i = 0; i < types.length; i++) {
    const typeText = types[i].textContent;
    const typeValue = typeText.split(' ')[0];
    const exerciseValue = typeText.split(' - ')[1].split(' (')[0];
    const secondsValue = (typeValue === "Rest") ? typeText.split('(')[1].split('s)')[0] : typeText.split(' (')[1].split('s, ')[0];
    const setsValue = (typeValue === "Rest") ? 0 : typeText.split(' (')[1].split('s, ')[1].split(' sets)')[0];

    workoutData.push({
      type: typeValue,
      exercise: exerciseValue,
      seconds: secondsValue,
      sets: setsValue,
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
