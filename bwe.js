const itemSelect = document.getElementById("item-select");
const exerciseSelect = document.getElementById("exercise-select");
const setsSelect = document.getElementById("sets-select");
const addItemBtn = document.getElementById("add-item-btn");
const itemsList = document.getElementById("items-list");
itemSelect.addEventListener("change", () => {
  const selectedType = itemSelect.value;
  if (selectedType) {
    const xhr = new XMLHttpRequest();
    xhr.open("GET", `get_exercises.php?type=${selectedType}`, true);
    xhr.onload = () => {
      if (xhr.status === 200) {
        const exercises = JSON.parse(xhr.responseText);
        exerciseSelect.innerHTML = "<option value='' disabled selected>Exercise</option>";
        exercises.forEach(exercise => {
          const option = document.createElement("option");
          option.value = exercise.name;
          option.textContent = exercise.name;
          if (selectedType !== 'Rest') {
            exerciseSelect.appendChild(option);
            exerciseSelect.disabled = false;
            setsSelect.disabled = false;
          }
        });
        if (selectedType === 'Rest') {
          exerciseSelect.disabled = true;
          setsSelect.disabled = true;
        }
      }
    };
    xhr.send();
  } else {
    exerciseSelect.innerHTML = "<option value='' disabled selected>Exercise</option>";
  }
});
document.getElementById("item-select").focus();
addItemBtn.addEventListener("click", () => {
  const itemValue = itemSelect.value;
  const exerciseValue = exerciseSelect.value;
  const secondsInput = document.querySelector('input[name="seconds"]');
  const setsInput = document.querySelector('input[name="sets"]');
  const secondsValue = secondsInput.value;
  const setsValue = setsInput.value;
  const $addItemBtn = $('#add-item-btn');
  const selectedListItem = $(".selected");
  const itemsArray = [];
  if (itemValue === "Rest" && secondsValue) {
    if (!$(".selected").length > 0) {
      itemsArray.push({
        item: itemValue,
        seconds: secondsValue,
      });
      const newItem = document.createElement("li");
      newItem.innerHTML = `${itemValue} - (${secondsValue}s)`;
      newItem.style.backgroundColor = "#454500";
      itemsList.appendChild(newItem);
      itemSelect.value = "";
      secondsInput.value = "";
    } else {
        itemsArray.push({
          item: itemValue,
          seconds: secondsValue,
        });
        selectedListItem.html(`${itemValue} - (${secondsValue}s)`)
        itemSelect.value = "";
        secondsInput.value = "";
        selectedListItem.css('background-color', '#454500');
        selectedListItem.removeClass('selected');  
    }
  } else {
      exerciseSelect.disabled = false;
      setsSelect.disabled = false;
      if (itemValue && exerciseValue && secondsValue && setsValue) {
        if (!$(".selected").length > 0) {
        itemsArray.push({
          item: itemValue,
          exercise: exerciseValue,
          seconds: secondsValue,
          sets: setsValue,
        });
        const newItem = document.createElement("li");
        newItem.innerHTML = `${itemValue} - ${exerciseValue} (${secondsValue}s, ${setsValue} sets)`;
        const newNumber = itemsList.children.length + 1;
        newItem.setAttribute('value', newNumber);
        itemsList.appendChild(newItem);
        itemSelect.value = "";
        exerciseSelect.value = "";
        secondsInput.value = "";
        setsInput.value = "";
        } else {
          itemsArray.push({
            item: itemValue,
            exercise: exerciseValue,
            seconds: secondsValue,
            sets: setsValue,
          });
          selectedListItem.html(`${itemValue} - ${exerciseValue} (${secondsValue}s, ${setsValue} sets)`)
          itemSelect.value = "";
          exerciseSelect.value = "";
          secondsInput.value = "";
          setsInput.value = "";
          selectedListItem.css('background-color', '#3d3d3d');
          selectedListItem.removeClass('selected');      }
      } else {
        alert("Please enter all required information.");
      }
    }
  $addItemBtn.text('Add Item'); 
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
$(document).on('click', "#items-list li", function(event) {
    const itemText = this.innerText;
    const itemValue = itemText.split(' ')[0];
    const exerciseValue = itemText.split(' - ')[1].split(' (')[0];
    const secondsValue = (itemValue === "Rest") ? itemText.split('(')[1].split('s)')[0] : itemText.split(' (')[1].split('s, ')[0];
    const setsValue = (itemValue === "Rest") ? 0 : itemText.split(' (')[1].split('s, ')[1].split(' sets)')[0];
    const itemSelect = document.getElementById("item-select");
    const secondsInput = document.querySelector('input[name="seconds"]');
    const setsInput = document.querySelector('input[name="sets"]');
    const $addItemBtn = $('#add-item-btn');
    $('.selected').removeClass('selected');
    this.classList.add('selected');
    $addItemBtn.text('Update Item');
    itemSelect.value = itemValue;
    secondsInput.value = secondsValue;
    if (itemValue === "Rest") {
      exerciseSelect.disabled = true;
      setsSelect.disabled = true;
    } else {
      exerciseSelect.disabled = false;
      setsSelect.disabled = false;
      exerciseSelect.innerHTML = "<option value='' disabled selected>Exercise</option>";
      const xhr = new XMLHttpRequest();
      xhr.open("GET", `get_exercises.php?type=${itemValue}`, true);
      xhr.onload = () => {
        if (xhr.status === 200) {
          const exercises = JSON.parse(xhr.responseText);
          exercises.forEach(exercise => {
            const option = document.createElement("option");
            option.value = exercise.name;
            option.textContent = exercise.name;
            exerciseSelect.appendChild(option);
            exerciseSelect.value = exerciseValue;
          });
        }
      };
      xhr.send();
      setsInput.value = setsValue;
    }
    const itemsArray = [];
    itemsArray.push({
      item: itemValue,
      exercise: exerciseValue,
      seconds: secondsValue,
      sets: setsValue,
    });
  });  
$(document).on('click', "#items-list li.selected", function(event) {
  const $addItemBtn = $('#add-item-btn');
  $(this).removeClass('selected');
  exerciseSelect.disabled = false;
  setsSelect.disabled = false;
  $addItemBtn.text('Add Item');
});

const outputListItemsBtn = document.querySelector("#output-list-items-btn");
if (outputListItemsBtn) {
  outputListItemsBtn.addEventListener("click", () => {
    const itemsList = document.getElementById("items-list");
    const items = itemsList.children;
    for (let i = 0; i < items.length; i++) {
      console.log(items[i].textContent);
    }
  });
} else {
  console.log("The element with the id 'output-list-items-btn' does not exist.");
}
