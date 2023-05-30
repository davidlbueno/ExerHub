saveWorkoutBtn = document.getElementById("save-workout-btn");
saveWorkoutBtn.addEventListener("click", () => {
  const urlParams = new URLSearchParams(window.location.search);
  const workoutId = urlParams.get('workout_id');
  const workoutName = document.getElementById("workout-name").value;
  const workoutList = document.getElementById("workout-list");
  const types = workoutList.children;
  const workoutData = [];

  for (let i = 0; i < types.length; i++) {
    const typeText = types[i].textContent;
    const typeValue = typeText.split(' ')[0];

    if (typeValue === 'Rest') {
      const secondsText = typeText.match(/\((\d+)s\)/)[1];
      const secondsValue = parseInt(secondsText, 10);

      workoutData.push({
        type: typeValue,
        exercise: 'Rest',
        seconds: secondsValue,
      });

      continue;
    }

    const exerciseValue = typeText.split(' - ')[1].split(' (')[0];
    const secondsText = typeText.match(/\((\d+)s\)/)[1];
    const secondsValue = parseInt(secondsText, 10);

    workoutData.push({
      type: typeValue,
      exercise: exerciseValue,
      seconds: secondsValue,
    });
  }

  const xhr = new XMLHttpRequest();
  console.log(workoutId, workoutName, workoutData);
  xhr.open("POST", "php/update_workout.php", true);
  xhr.setRequestHeader("Content-Type", "application/json");
  xhr.onload = () => {
    if (xhr.status === 200) {
      // Handle the response from the PHP script if needed
      const redirectUrl = `workout.php?workout_id=${workoutId}&workout_name=${encodeURIComponent(workoutName)}`;
      window.location.href = redirectUrl;
      console.log(xhr.responseText);
    } else {
      // Handle errors
      console.error(xhr.responseText);
    }
  };

  const payload = {
    workoutId: workoutId,
    workoutName: workoutName,
    workoutData: workoutData,
  };

  xhr.send(JSON.stringify(payload));
});
