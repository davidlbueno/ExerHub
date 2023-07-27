// Select elements
const typeSelect = document.getElementById("type-select");
const exerciseSelect = document.getElementById("exercise-select");
const setsSelect = document.getElementById("sets-select");
const addItemBtn = document.getElementById("add-type-btn");
const clearListBtn = document.getElementById("clear-list-btn");
const typesList = document.getElementById("workout-list");
const secondsInput = document.querySelector('input[name="seconds"]');
const setsInput = document.querySelector('input[name="sets"]');
const warmupInput = document.querySelector('input[name="warmup"]');
const saveWorkoutBtn = document.getElementById("save-workout-btn");
const workoutNameInput = document.getElementById("workout-name");
const cancelWorkoutBtn = document.getElementById("cancel-workout-btn");

// Disable saveWorkoutBtn initially
saveWorkoutBtn.disabled = true;

// Function to update exercise select options
async function updateExerciseSelect(selectedType, callback) {
  const response = await fetch(`php/get_exercises.php?type=${selectedType}`);
  const exercises = await response.json();

  exerciseSelect.innerHTML = `
    <option value="" disabled selected>Exercise</option>
    ${exercises.map(exercise => `<option value="${exercise.name}">${exercise.name}</option>`).join('')}
  `;
  exerciseSelect.disabled = selectedType === 'Rest';
  setsSelect.disabled = (selectedType === 'Rest') || $('.selected').length > 0;

  if (callback) {
    callback();
  }
}

// Event listener for typeSelect change
typeSelect.addEventListener("change", () => {
  updateExerciseSelect(typeSelect.value);
});

// Event listener for addItemBtn click
addItemBtn.addEventListener("click", () => {
  const $addItemBtn = $('#add-type-btn');
  const selectedListItem = $(".selected");

  if (typeSelect.value === 'Rest' || (typeSelect.value && exerciseSelect.value && secondsInput.value && (!setsInput.disabled || setsInput.value != null))) {
    if (typeSelect.value === 'Rest') {
      if (selectedListItem.length > 0) {
        // Update existing list item
        selectedListItem.html(`${typeSelect.value} - (${secondsInput.value}s)`);
        selectedListItem.css('background-color', '#3d3d3d');
        selectedListItem.removeClass('selected');
      } else {
        // Add a new list item for Rest type
        const newItem = document.createElement('li');
        const newNumber = typesList.children.length + 1;
        newItem.setAttribute('value', newNumber);
        typesList.appendChild(newItem);
        newItem.innerHTML = `${typeSelect.value} - (${secondsInput.value}s)`;
        newItem.classList.add('rest');
      }
    } else {
      if (selectedListItem.length > 0) {
        // Update existing list item
        selectedListItem.html(`${typeSelect.value} - ${exerciseSelect.value} (${secondsInput.value}s)${warmupInput.checked ? ' - Warmup' : ''}`);
        selectedListItem.css('background-color', '#3d3d3d');
        selectedListItem.toggleClass('warmup', warmupInput.checked);
        selectedListItem.removeClass('selected');
      } else {
        // Add a new list item for non-Rest type
        const newNumber = typesList.children.length + 1;
        saveWorkoutBtn.disabled = false;

        for (let i = 0; i < setsInput.value; i++) {
          const newItem = document.createElement('li');
          newItem.setAttribute('value', newNumber);
          typesList.appendChild(newItem);
          newItem.innerHTML = `${typeSelect.value} - ${exerciseSelect.value} (${secondsInput.value}s)${warmupInput.checked ? ' - Warmup' : ''}`;
          newItem.classList.toggle('warmup', warmupInput.checked);
        }
      }
    }
  } else {
    alert("Please enter all required information.");
    return;
  }

  $addItemBtn.text('Add Item');
  clearFields();
  typeSelect.focus();
});

// Sortable functionality
$(".sortable").sortable({
  revert: true,
  update: function() {
    const types = $(this).children();
    types.each((index, element) => {
      element.setAttribute('value', index + 1);
    });
    saveWorkoutBtn.disabled = false;
  }
});

// Event delegation for selecting items in workout-list
$(document).on('click', "#workout-list li", function(event) {
  const typeText = this.innerText;
  const typeValue = typeText.split(' ')[0];
  const exerciseValue = typeText.split(' - ')[1].split(' (')[0];
  const secondsValue = parseInt(typeText.match(/\((\d+)s\)/)[1]);
  const warmupValue = this.classList.contains('warmup') ? 'on' : 'off';
  const $addItemBtn = $('#add-type-btn');
  const removeItemBtn = document.createElement('button');
  removeItemBtn.textContent = 'Del';
  removeItemBtn.classList.add('copy-del-btn');
  removeItemBtn.addEventListener('click', function() {
    this.parentElement.remove();
    clearFields();
    saveWorkoutBtn.disabled = false;
  });

  const exerciseSelect = document.querySelector('select[name="exercise"]');

  setsSelect.disabled = true;

  if (event.target.classList.contains('selected')) {
    $(this).removeClass('selected');
    exerciseSelect.disabled = false;
    $addItemBtn.text('Add Item');
    $(this).find('.copy-del-btn').remove();
    clearFields();
    saveWorkoutBtn.disabled = false;
  } else {
    $('.selected').removeClass('selected').find('.copy-del-btn').remove();
    this.classList.add('selected');
    const duplicateItemBtn = document.createElement('button');
    duplicateItemBtn.textContent = 'Copy';
    duplicateItemBtn.classList.add('copy-del-btn');
    duplicateItemBtn.addEventListener('click', function() {
      const newListItem = this.parentElement.cloneNode(true);
      newListItem.classList.remove('selected');
      newListItem.setAttribute('value', typesList.children.length + 1);
      typesList.appendChild(newListItem);
      // Remove the copy and delete buttons from the new list item
      const copyDelButtons = newListItem.querySelectorAll('.copy-del-btn');
      copyDelButtons.forEach(button => {
        button.remove();
      });
    
      clearFields();
      saveWorkoutBtn.disabled = false;
    });
    
    this.appendChild(removeItemBtn);
    this.appendChild(duplicateItemBtn);
    $addItemBtn.text('Update Item');
    typeSelect.value = typeValue;
    secondsInput.value = secondsValue;
    warmupInput.checked = warmupValue === 'on';
    exerciseSelect.disabled = false;
    updateExerciseSelect(typeValue, () => {
      exerciseSelect.value = exerciseValue;
    });
    saveWorkoutBtn.disabled = true;
  }
});

// Function to calculate total workout time and display it in the workout-length div
function calculateWorkoutLength() {
  const workoutLength = document.getElementById("workout-length");
  let totalSeconds = 0;
  for (let i = 0; i < typesList.children.length; i++) {
    const type = typesList.children[i].innerText;
    const seconds = parseInt(type.match(/\((\d+)s\)/)[1]);
    totalSeconds += seconds;
  }
  workoutLength.innerText = totalSeconds;
  if (totalSeconds > 0) {
    // Format the number of seconds in totalSeconds to MM:SS
    const minutes = Math.floor(totalSeconds / 60);
    const seconds = totalSeconds % 60;
    workoutLength.innerText = `${minutes}:${seconds < 10 ? '0' : ''}${seconds}`;
  }
}

// Function to clear input fields
function clearFields() {
  calculateWorkoutLength();
  typeSelect.value = "";
  exerciseSelect.value = "";
  secondsInput.value = "";
  setsInput.value = "";
  warmupInput.checked = false;
  typeSelect.focus();
}

// Event listener for clearListBtn click
clearListBtn.addEventListener("click", clearList);

cancelWorkoutBtn.addEventListener("click", () => {
  const referringUrl = document.referrer;
  const referringUri = referringUrl.split('/').slice(3).join('/');
  window.location.href = referringUri;
});

// Function to clear the types list
function clearList() {
  typesList.innerHTML = "";
  saveWorkoutBtn.disabled = true;
  clearFields();
}

// Set focus on workout name input
workoutNameInput.focus();
