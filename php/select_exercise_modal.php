<!-- modal -->
<div id="addItemModal" class="modal dark-modal modal-overflow">
      <div class="modal-content">
        <h5 style="margin-bottom: 5px;">Add Item</h5>
        <div>
          <div style="margin-bottom: 5px;">
            <select title="type-select" name="type" id="type-select">
              <option value="" disabled selected>Item</option>
              <option value="Push">Push</option>
              <option value="Pull">Pull</option>
              <option value="Legs">Legs</option>
              <option value="Core">Core</option>
              <option value="Rest">Rest</option>
            </select>
          </div>
          <div style="margin-bottom: 5px;">
            <select title="exercise-select" name="exercise" id="exercise-select" disabled>
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
  const warmupCheckbox = document.getElementById("warmup");
  const repsSelect = document.getElementById("reps-select");

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
      ${exercises.map(exercise => `<option value="${exercise.id}">${exercise.name}</option>`).join('')}`;
    exerciseSelect.disabled = selectedType === 'Rest';
    repsSelect.disabled = selectedType === 'Rest';
    warmupCheckbox.disabled = selectedType === 'Rest';

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

$('#modal-save-item').click(function() {
  const type = $('#type-select').val();
  const exerciseOption = type === "Rest" ? null : $('#exercise-select option:selected');
  const exercise = exerciseOption ? exerciseOption.text() : "";
  const exerciseId = exerciseOption ? exerciseOption.val() : null;
  const seconds = $('input[name="seconds"]').val();
  const isWarmup = $('#warmup').is(':checked');
  const isRest = type === "Rest";
  const reps = $('#reps-select').val();
  const sets = $('#sets-select').prop('disabled') ? 1 : parseInt($('#sets-select').val(), 10);

  if (editingItem) {
    // Update the text and data attributes of the editingItem
    const newText = `<strong>${type}</strong> - ${exercise} (${seconds}s, ${reps} reps)`;
    editingItem.find('div:first').html(newText);

    // Update the data attributes
    editingItem.attr('data-exercise-time', seconds);
    editingItem.attr('data-exercise-reps', reps);
    editingItem.attr('data-exercise-id', exerciseId);

    // Update the warmup class if needed
    editingItem.removeClass('warmup');
    if (isWarmup) {
      editingItem.addClass('warmup');
    }

    // update the rest class if needed
    editingItem.removeClass('rest');
    if (isRest) {
      editingItem.addClass('rest');
    }

    // Add the edit, copy, and delete icons
    const iconsDiv = '<div style="display: inline-block; float: right; width: 80px; z-index: 1;"><i class="material-icons edit-icon">edit</i> <i class="material-icons copy-icon">file_copy</i> <i class="material-icons delete-icon">delete</i></div>';
    editingItem.find('div:first').append(iconsDiv);

    // Remove the exercise-details div if the type is "Rest"
    if (isRest) {
      editingItem.find('.exercise-details').remove();
    } else {
      // Ensure the exercise-details div exists if the type is not "Rest"
      if (editingItem.find('.exercise-details').length === 0) {
        const exerciseDetailsDiv = `<div class='exercise-details' style='top: 0px; display: block; position: relative;'>Actual Reps: <input type='number' id='repsInput' max='999' placeholder='Reps' style='width: 40px; height: 30px' value=${reps}>Actual Seconds: <input type='number' id='secondsInput' max='999' step='5' placeholder='Seconds' style='width: 40px; height: 30px' value=${seconds}></div>`;
        editingItem.append(exerciseDetailsDiv);
      }
    }
    
    // Reset editingItem to null
    editingItem = null;
  } else {
    // Add the new item to the list
    const newItem = `<li data-exercise-id='${exerciseId}' data-exercise-time='${seconds}' data-exercise-reps='${reps}' class='${isWarmup ? 'warmup' : ''} ${isRest ? 'rest' : ''}' style='white-space: nowrap;'><div style='display: inline-block; width: calc(100% - 80px); overflow: hidden; white-space: nowrap;'><strong>${type}</strong> - ${exercise} (${seconds}s, ${reps} reps)</div><div style='display: inline-block; width: 80px; z-index: 1;'><i class='material-icons edit-icon'>edit</i> <i class='material-icons copy-icon'>file_copy</i> <i class='material-icons delete-icon'>delete</i></div></li>`;
    $('ol').append(newItem);
  }
  var instance = M.Modal.getInstance(document.getElementById("addItemModal"));
  instance.close();
});

</script>