const urlParams = new URLSearchParams(window.location.search);
const workoutId = urlParams.get('workout_id');
const workoutName = urlParams.get('workout_name');
const redirectUrl = `workout.php?workout_id=${workoutId}&workout_name=${encodeURIComponent(workoutName)}`;
saveWorkoutBtn = document.getElementById("save-workout-btn");
const deleteWorkoutBtn = document.getElementById("delete-workout-btn");
const cancelWorkoutBtn = document.getElementById("cancel-workout-btn");


saveWorkoutBtn.addEventListener("click", () => {
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
      window.location.href = redirectUrl;
      console.log(xhr.responseText);
    } else {
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

cancelWorkoutBtn.addEventListener("click", () => {
  window.location.href = redirectUrl;
});

deleteWorkoutBtn.addEventListener("click", () => {
  if (confirm(`Are you sure you want to delete the workout: ${workoutName}?`)) {
    const xhr = new XMLHttpRequest();
    xhr.open("POST", "php/delete_workout.php", true);
    xhr.setRequestHeader("Content-Type", "application/json");
    xhr.onload = () => {
      if (xhr.status === 200) {
        window.location.href = '../workouts.php';
        console.log(xhr.responseText);
      } else {
        console.error(xhr.responseText);
      }
    };

    const payload = {
      workoutId: workoutId,
    };

    xhr.send(JSON.stringify(payload));
  }
});
