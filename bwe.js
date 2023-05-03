let typeSelect = document.getElementById("type-select");
let exerciseSelect = document.getElementById("exercise-select");
let setsSelect = document.getElementById("sets-select");
let addItemBtn = document.getElementById("add-type-btn");
let clearListBtn = document.getElementById("clear-list-btn");
let typesList = document.getElementById("types-list");
let secondsInput = document.querySelector('input[name="seconds"]');
let setsInput = document.querySelector('input[name="sets"]');
let saveWorkoutBtn = document.getElementById("save-workout-btn");
let userName = window.sessionVars.userName;
saveWorkoutBtn.disabled = true;
document.getElementById("workout-name").focus();
function updateExerciseSelect(selectedType, callback) {
  const xhr = new XMLHttpRequest();
  xhr.open("GET", `php/get_exercises.php?type=${selectedType}`, true);
  xhr.onload = () => {
    if (xhr.status === 200) {
      const exercises = JSON.parse(xhr.responseText);
      exerciseSelect.innerHTML = `
      <option value="" disabled selected>Exercise</option>
      ${exercises.map(exercise => `
          <option value="${exercise.name}">${exercise.name}</option>
      `).join('')}
      `;
      exerciseSelect.disabled = selectedType === 'Rest';
      setsSelect.disabled = selectedType === 'Rest';
      if (callback) {
        callback();
      }
    }
  };
  xhr.send();
}
typeSelect.addEventListener("change", () => {
  updateExerciseSelect(typeSelect.value); 
});
addItemBtn.addEventListener("click", () => {
  let $addItemBtn = $('#add-type-btn');
  let selectedListItem = $(".selected");
  let typesArray = [];
  let newItem = document.createElement("li");
  if (typeSelect.value === "Rest" && secondsInput.value) {
    typesArray.push({
        type: typeSelect.value,
        seconds: secondsInput.value,
      });
    if (!$(".selected").length > 0) {
      newItem.innerHTML = `${typeSelect.value} - (${secondsInput.value}s)`;
      newItem.style.backgroundColor = "#454500";
      typesList.appendChild(newItem);
      clearFields();
      saveWorkoutBtn.disabled = false;
    } else {
        selectedListItem.html(`${typeSelect.value} - (${secondsInput.value}s)`)
        clearFields();
        selectedListItem.css('background-color', '#454500');
        selectedListItem.removeClass('selected');  
    }
  } else {
    exerciseSelect.disabled = false;
    setsSelect.disabled = false;
    if (typeSelect.value && exerciseSelect.value && secondsInput.value && setsInput.value) {
      typesArray.push({
        type: typeSelect.value,
        exercise: exerciseSelect.value,
        seconds: secondsInput.value,
        sets: setsInput.value,
    });
    if (!$(".selected").length > 0) {
      const newItem = document.createElement("li");
      newItem.innerHTML = `${typeSelect.value} - ${exerciseSelect.value} (${secondsInput.value}s, ${setsInput.value} sets)`;
      const newNumber = typesList.children.length + 1;
      newItem.setAttribute('value', newNumber);
      typesList.appendChild(newItem);
      saveWorkoutBtn.disabled = false;
    } else {
      selectedListItem.html(`${typeSelect.value} - ${exerciseSelect.value} (${secondsInput.value}s, ${setsInput.value} sets)`)
      selectedListItem.css('background-color', '#3d3d3d');
      selectedListItem.removeClass('selected');      }
    } else {
      alert("Please enter all required information.");
    }
  }
  $addItemBtn.text('Add Item'); 
  clearFields();
  document.getElementById("type-select").focus();
});
$(function() {
  $( ".sortable" ).sortable({
    revert: true,
    update: function() {
      const typesList = $(this);
      const types = typesList.children();
      for (let i = 0; i < types.length; i++) {
        types[i].setAttribute('value', i + 1);
      }
    }
  });
});
function clearFields() {
  typeSelect.value = "";
  exerciseSelect.value = "";
  secondsInput.value = "";
  setsInput.value = "";
}
$(document).on('click', "#types-list li", function(event) {
  const typeText = this.innerText;
  const typeValue = typeText.split(' ')[0];
  const exerciseValue = typeText.split(' - ')[1].split(' (')[0];
  const secondsValue = (typeValue === "Rest") ? typeText.split('(')[1].split('s)')[0] : typeText.split(' (')[1].split('s, ')[0];
  const setsValue = (typeValue === "Rest") ? 0 : typeText.split(' (')[1].split('s, ')[1].split(' sets)')[0];
  const secondsInput = document.querySelector('input[name="seconds"]');
  const setsInput = document.querySelector('input[name="sets"]');
  const $addItemBtn = $('#add-type-btn');
  const removeItemBtn = document.createElement('button');
  removeItemBtn.textContent = 'Del';
  removeItemBtn.classList.add('copy-del-btn');
  removeItemBtn.addEventListener('click', function() {
    this.parentElement.remove();
    clearFields();
    saveWorkoutBtn.disabled = false;
    document.getElementById("type-select").focus();
  });
  const exerciseSelect = document.querySelector('select[name="exercise"]');
  if (event.target.classList.contains('selected')) {
    $(this).removeClass('selected');
    exerciseSelect.disabled = false;
    setsSelect.disabled = false;
    $addItemBtn.text('Add Item');
    const removeItemBtn = $(this).find('.copy-del-btn');
    removeItemBtn.remove();
    saveWorkoutBtn.disabled = false;
  } else {
    $('.selected').removeClass('selected').find('.copy-del-btn').remove();
    this.classList.add('selected');
    const duplicateItemBtn = document.createElement('button');
    duplicateItemBtn.textContent = 'Copy';
    duplicateItemBtn.classList.add('copy-del-btn');
    duplicateItemBtn.addEventListener('click', function() {
      const newItem = document.createElement("li");
      if (typeValue === "Rest") {
        newItem.style.backgroundColor = "#454500";
        newItem.innerHTML = `${typeSelect.value} - (${secondsInput.value}s)`;
      } else {
      newItem.innerHTML = `${typeValue} - ${exerciseValue} (${secondsValue}s, ${setsValue} sets)`;
      }
      const newNumber = typesList.children.length + 1;
      newItem.setAttribute('value', newNumber);
      typesList.appendChild(newItem);
    });
    this.appendChild(removeItemBtn);
    this.appendChild(duplicateItemBtn);
    $addItemBtn.text('Update Item');
    typeSelect.value = typeValue;
    secondsInput.value = secondsValue;
    if (typeValue === "Rest") {
      exerciseSelect.disabled = true;
      setsSelect.disabled = true;
    } else {
      exerciseSelect.disabled = false;
      setsSelect.disabled = false;
      updateExerciseSelect(typeValue);
      updateExerciseSelect(typeSelect.value, function() {
        exerciseSelect.value = exerciseValue;
      });
      setsInput.value = setsValue;
    }
    saveWorkoutBtn.disabled = true;
    const typesArray = [];
    typesArray.push({
      type: typeValue,
      exercise: exerciseValue,
      seconds: secondsValue,
      sets: setsValue,
    });
  }
});
function clearList() {
  typesList.innerHTML = ""; // Clear the types list
  saveWorkoutBtn.disabled = true;
}
