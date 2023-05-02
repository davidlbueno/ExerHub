let itemSelect = document.getElementById("item-select");
let exerciseSelect = document.getElementById("exercise-select");
let setsSelect = document.getElementById("sets-select");
let addItemBtn = document.getElementById("add-item-btn");
let clearListBtn = document.getElementById("clear-list-btn");
let itemsList = document.getElementById("items-list");
let secondsInput = document.querySelector('input[name="seconds"]');
let setsInput = document.querySelector('input[name="sets"]');
let saveWorkoutBtn = document.getElementById("save-workout-btn");
let userName = window.sessionVars.userName;
console.log(userName);
saveWorkoutBtn.disabled = true;
document.getElementById("workout-name").focus();
function updateExerciseSelect(selectedType, callback) {
  const xhr = new XMLHttpRequest();
  xhr.open("GET", `get_exercises.php?type=${selectedType}`, true);
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
itemSelect.addEventListener("change", () => {
  updateExerciseSelect(itemSelect.value); 
});
addItemBtn.addEventListener("click", () => {
  let $addItemBtn = $('#add-item-btn');
  let selectedListItem = $(".selected");
  let itemsArray = [];
  let newItem = document.createElement("li");
  if (itemSelect.value === "Rest" && secondsInput.value) {
    itemsArray.push({
        item: itemSelect.value,
        seconds: secondsInput.value,
      });
    if (!$(".selected").length > 0) {
      newItem.innerHTML = `${itemSelect.value} - (${secondsInput.value}s)`;
      newItem.style.backgroundColor = "#454500";
      itemsList.appendChild(newItem);
      clearFields();
      saveWorkoutBtn.disabled = false;
    } else {
        selectedListItem.html(`${itemSelect.value} - (${secondsInput.value}s)`)
        clearFields();
        selectedListItem.css('background-color', '#454500');
        selectedListItem.removeClass('selected');  
    }
  } else {
    exerciseSelect.disabled = false;
    setsSelect.disabled = false;
    if (itemSelect.value && exerciseSelect.value && secondsInput.value && setsInput.value) {
      itemsArray.push({
        item: itemSelect.value,
        exercise: exerciseSelect.value,
        seconds: secondsInput.value,
        sets: setsInput.value,
    });
    if (!$(".selected").length > 0) {
      const newItem = document.createElement("li");
      newItem.innerHTML = `${itemSelect.value} - ${exerciseSelect.value} (${secondsInput.value}s, ${setsInput.value} sets)`;
      const newNumber = itemsList.children.length + 1;
      newItem.setAttribute('value', newNumber);
      itemsList.appendChild(newItem);
      saveWorkoutBtn.disabled = false;
    } else {
      selectedListItem.html(`${itemSelect.value} - ${exerciseSelect.value} (${secondsInput.value}s, ${setsInput.value} sets)`)
      selectedListItem.css('background-color', '#3d3d3d');
      selectedListItem.removeClass('selected');      }
    } else {
      alert("Please enter all required information.");
    }
  }
  $addItemBtn.text('Add Item'); 
  clearFields();
  document.getElementById("item-select").focus();
});
$(function() {
  $( ".sortable" ).sortable({
    revert: true,
    update: function() {
      const itemsList = $(this);
      const items = itemsList.children();
      for (let i = 0; i < items.length; i++) {
        items[i].setAttribute('value', i + 1);
      }
    }
  });
});
function clearFields() {
  itemSelect.value = "";
  exerciseSelect.value = "";
  secondsInput.value = "";
  setsInput.value = "";
}
$(document).on('click', "#items-list li", function(event) {
  const itemText = this.innerText;
  const itemValue = itemText.split(' ')[0];
  const exerciseValue = itemText.split(' - ')[1].split(' (')[0];
  const secondsValue = (itemValue === "Rest") ? itemText.split('(')[1].split('s)')[0] : itemText.split(' (')[1].split('s, ')[0];
  const setsValue = (itemValue === "Rest") ? 0 : itemText.split(' (')[1].split('s, ')[1].split(' sets)')[0];
  const secondsInput = document.querySelector('input[name="seconds"]');
  const setsInput = document.querySelector('input[name="sets"]');
  const $addItemBtn = $('#add-item-btn');
  const removeItemBtn = document.createElement('button');
  removeItemBtn.textContent = 'Del';
  removeItemBtn.classList.add('copy-del-btn');
  removeItemBtn.addEventListener('click', function() {
    this.parentElement.remove();
    clearFields();
    document.getElementById("item-select").focus();
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
      if (itemValue === "Rest") {
        newItem.style.backgroundColor = "#454500";
        newItem.innerHTML = `${itemSelect.value} - (${secondsInput.value}s)`;
      } else {
      newItem.innerHTML = `${itemValue} - ${exerciseValue} (${secondsValue}s, ${setsValue} sets)`;
      }
      const newNumber = itemsList.children.length + 1;
      newItem.setAttribute('value', newNumber);
      itemsList.appendChild(newItem);
    });
    this.appendChild(removeItemBtn);
    this.appendChild(duplicateItemBtn);
    $addItemBtn.text('Update Item');
    itemSelect.value = itemValue;
    secondsInput.value = secondsValue;
    if (itemValue === "Rest") {
      exerciseSelect.disabled = true;
      setsSelect.disabled = true;
    } else {
      exerciseSelect.disabled = false;
      setsSelect.disabled = false;
      updateExerciseSelect(itemValue);
      updateExerciseSelect(itemSelect.value, function() {
        exerciseSelect.value = exerciseValue;
      });
      setsInput.value = setsValue;
    }
    saveWorkoutBtn.disabled = true;
    const itemsArray = [];
    itemsArray.push({
      item: itemValue,
      exercise: exerciseValue,
      seconds: secondsValue,
      sets: setsValue,
    });
  }
});
function clearList() {
  itemsList.innerHTML = ""; // Clear the items list
  saveWorkoutBtn.disabled = true;
}
clearListBtn.addEventListener("click", clearList);
saveWorkoutBtn.addEventListener("click", () => {
  const workoutName = document.getElementById("workout-name").value;
  const itemsList = document.getElementById("items-list");
  const items = itemsList.children;
  console.log(workoutName);
  for (let i = 0; i < items.length; i++) {
    console.log(items[i].textContent);
  }
});
