saveWorkoutBtn.addEventListener("click", () => {
  const workoutName = document.getElementById("workout-name").value;
  const itemsList = document.getElementById("items-list");
  const items = itemsList.children;

  // Create an array to store the exercise data
  const workoutData = [];

  for (let i = 0; i < items.length; i++) {
    const itemText = items[i].textContent;
    const itemValue = itemText.split(' ')[0];
    const exerciseValue = itemText.split(' - ')[1].split(' (')[0];
    const secondsValue = (itemValue === "Rest") ? itemText.split('(')[1].split('s)')[0] : itemText.split(' (')[1].split('s, ')[0];
    const setsValue = (itemValue === "Rest") ? 0 : itemText.split(' (')[1].split('s, ')[1].split(' sets)')[0];

    workoutData.push({
      item: itemValue,
      exercise: exerciseValue,
      seconds: secondsValue,
      sets: setsValue,
    });
  }

  // Send an AJAX request to the PHP script
  const xhr = new XMLHttpRequest();
  xhr.open("POST", "save_workout.php", true);
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
  xhr.send(JSON.stringify({ workoutName, workoutData }));
});
