var ctx = document.getElementById('graphCanvas').getContext('2d');

var currentData = {
  labels: graphData.map(function(o) { return o.name; }),
  datasets: [{
    label: 'Current Workout',
    type: 'bar', // Set chart type as bar
    data: graphData.map(function(o) { return o.reps; }),
    backgroundColor: 'rgba(75, 192, 192, 0.2)',
    borderColor: 'rgba(75, 192, 192, 1)',
    borderWidth: 1
  }]
};

var previousData = {
  labels: prevGraphData.map(function(o) { return o.name; }),
  datasets: [{
    label: 'Previous Workout',
    type: 'line', // Set chart type as line
    data: prevGraphData.map(function(o) { return o.reps; }),
    backgroundColor: 'rgba(0, 0, 0, 0)', // Transparent background color for line chart
    borderColor: 'rgba(192, 75, 75, 1)',
    borderWidth: 1,
    borderDash: [5, 5], // Set borderDash to create dotted lines
    pointStyle: 'line', // Display the data points as lines
    fill: false // Do not fill the area under the line
  }]
};

// Find the maximum reps value
var maxReps = Math.max(
  Math.max.apply(null, graphData.map(function(o) { return o.reps; })),
  Math.max.apply(null, prevGraphData.map(function(o) { return o.reps; }))
);

// Round the x-axis maximum value to the next ten
var maxXAxis = Math.ceil(maxReps / 10) * 10;

var options = {
  responsive: true,
  scales: {
    x: {
      beginAtZero: true,
      max: maxXAxis // Set the x-axis maximum value
    },
    y: {
      categoryPercentage: 1.0,
      barPercentage: 0.8
    }
  },
  indexAxis: 'y', // Set the index axis to 'y' for horizontal bars
  plugins: {
    legend: {
      display: false
    },
    title: {
      display: true,
      text: 'Current vs Previous Reps', // Set the chart title
      font: {
        size: 20,
        weight: 'bold'
      }
    }
  }
};

// Merge currentData and previousData to create combinedData
var combinedData = {
  labels: currentData.labels,
  datasets: [previousData.datasets[0], currentData.datasets[0]] // Reverse the order of datasets
};

// Create a new Chart instance with combinedData
new Chart(ctx, {
  type: 'bar',
  data: combinedData,
  options: options
});
