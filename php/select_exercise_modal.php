<!-- modal -->
<div id="addItemModal" class="modal dark-modal">
      <div class="modal-content">
        <h5 style="margin-bottom: 5px;">Add Item</h5>
        <div>
          <div style="margin-bottom: 5px;">
            <select name="type" id="type-select">
              <option value="" disabled selected>Item</option>
              <option value="Push">Push</option>
              <option value="Pull">Pull</option>
              <option value="Legs">Legs</option>
              <option value="Core">Core</option>
              <option value="Rest">Rest</option>
            </select>
          </div>
          <div style="margin-bottom: 5px;">
            <select name="exercise" id="exercise-select" disabled>
              <option value="" disabled selected>Exercise</option>
            </select>
          </div>
          <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 5px;">
            <input type="number" name="seconds" min="0" max="300" step="5" placeholder="Seconds" style="width:32%;">
            <input type="number" name="sets" id="sets-select" min="0" max="10" step="1" placeholder="Sets" style="width:32%;">
            <input type="number" name="reps" id="reps-select" min="0" max="50" step="1" placeholder="Reps" style="width:32%;">
          </div>
          <div style="margin-bottom: 5px;">
            <label>
              <input type="checkbox" name="warmup" id="warmup" style="width:100%;">
              <span>Warmup</span>
            </label>
          </div>  
          <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 5px;">      
            <button id="modal-save-item" class="btn" style="width: 48%;">Save</button>
            <button id="modal-cancel-item" class="btn modal-close" style="width: 48%;">Cancel</button>
          </div>
        </div>
          <i id="modal-closeBtn" class="material-icons close-btn" style="margin-bottom: 5px;">close</i>
      </div>
    </div>
    <!-- modal -->
    <script>
  const typeSelect = document.getElementById("type-select");
  const exerciseSelect = document.getElementById("exercise-select");
  const setsSelect = document.getElementById("sets-select");

  let editingItem = null;

// Event listener for typeSelect change
typeSelect.addEventListener("change", () => {
  updateExerciseSelect(typeSelect.value);
});

// Function to update exercise select options
async function updateExerciseSelect(selectedType, callback) {
const response = await fetch(`php/get_exercises.php?type=${selectedType}`);
const exercises = await response.json();

exerciseSelect.innerHTML = 
`<option value="" disabled selected>Exercise</option>
  ${exercises.map(exercise => `<option value="${exercise.name}">${exercise.name}</option>`).join('')}`;
  // add exercise id as data attribute
  exercises.forEach(exercise => {
    const option = $(`#exercise-select option[value='${exercise.name}']`);
    option.data('id', exercise.id);
  });
exerciseSelect.disabled = selectedType === 'Rest';

if (callback) {
  callback();
}
}

//Initialize the modal
var elems = document.querySelectorAll('.modal');
var instances = M.Modal.init(elems, {
onOpenEnd: function() {
  typeSelect.focus();
}
});

// Add event listener for the close button
document.getElementById("modal-closeBtn").addEventListener("click", function() {
var instance = M.Modal.getInstance(document.getElementById("addItemModal"));
instance.close();
});

// Add event listener for the "Add" button in the modal
$('#modal-save-item').click(function() {
  const type = $('#type-select').val();
  const exerciseOption = type === "Rest" ? null : $('#exercise-select option:selected');
  const exercise = exerciseOption ? exerciseOption.text() : "";
  const exerciseId = exerciseOption ? exerciseOption.data('id') : null;
  const seconds = $('input[name="seconds"]').val();
  const sets = parseInt($('#sets-select').val(), 10);
  const isWarmup = $('#warmup').is(':checked');
  const reps = $('#reps-select').val();

  for (let i = 0; i < sets; i++) {
    let newItem;
    if (type === "Rest") {
      newItem = `<li class='rest'><strong>Rest</strong> - (</span><input type='number' name='exercise_time[]' class='exercise-time' value='${seconds}' min='0' step='5' style='width: 50px;'>s)</li>`;
    } else {
      if (isWarmup) {
        newItem = `<li data-exercise-id='${exerciseId}' class='warmup'><strong>${type}</strong> - ${exercise} (</span><input type='number' name='exercise_time[]' class='exercise-time' value='${seconds}' min='0' step='5' style='width: 50px;'>s, <input type='number' name='exercise_reps[]' class='exercise-reps' value='${reps}' min='0' style='width: 30px;'> reps) </li>`;
      } else {
        newItem = `<li data-exercise-id='${exerciseId}'><strong>${type}</strong> - ${exercise} (</span><input type='number' name='exercise_time[]' class='exercise-time' value='${seconds}' min='0' step='5' style='width: 50px;'>s, <input type='number' name='exercise_reps[]' class='exercise-reps' value='${reps}' min='0' style='width: 30px;'> reps)</li>`;
      }
    }
    if (editingItem) {
      editingItem.replaceWith(newItem);
      editingItem = null;
    } else {
      $('ol').append(newItem);
    }
    $('ol').append(newItem);
  }

  var instance = M.Modal.getInstance($('#addItemModal'));
  instance.close(); 
  
  // Function to disable reps input for 'Rest' type
  function disableRepsForRest() {
    $('.type-select').each(function() {
      const type = $(this).val();
      const repsInput = $(this).closest('tr').find('.reps-input')[0];
      if (type === 'Rest') {
        repsInput.disabled = true;
      } else {
        repsInput.disabled = false;
      }
    });
  }
  // Close the modal
  var instance = M.Modal.getInstance($('#addItemModal'));
    instance.close();
});
</script>