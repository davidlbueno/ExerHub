document.addEventListener('DOMContentLoaded', function() {
  var ctx = document.getElementById('graphCanvas').getContext('2d');

  var graphLabels = [];
  var graphIntensities = [];
  var graphColors = [];
  var graphDurations = []; // Array to store the durations

  console.log(graphData);

  var sumDurations = 0; // Variable to store the sum of durations

  for (var i = 0; i < graphData.length; i++) {
    var item = graphData[i];
    graphLabels.push(item.name);
    graphIntensities.push(item.intensity);
    graphDurations.push(item.duration); // Store the duration of each item
    sumDurations += item.duration; // Calculate the sum of durations

    // Set the color to grey for Rest items, and the default color for others
    var color = item.name === 'Rest' ? 'rgba(192, 192, 192, 0.5)' : 'rgba(75, 192, 192, 0.5)';
    graphColors.push(color);
  }

  new Chart(ctx, {
    type: 'bar',
    data: {
      labels: graphLabels,
      datasets: [{
        label: 'Intensity',
        data: graphIntensities,
        backgroundColor: graphColors,
        borderColor: 'rgba(75, 192, 192, 1)',
        borderWidth: 1,
        barPercentage: 1, // Set barPercentage to 1 to allow variable width columns
        categoryPercentage: 1, // Set categoryPercentage to 1 to allow variable width columns
        barThickness: function(context) {
          var index = context.dataIndex;
          var duration = graphDurations[index];
          var maxDuration = Math.max(...graphDurations);
          var maxWidth = 50; // Set the maximum width of the bar (adjust as needed)
          var percentage = duration / maxDuration;
          return Math.round(maxWidth * percentage); // Return the desired thickness in pixels
        }
      }]
    },
    options: {
      indexAxis: 'x',
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: {
          display: false
        },
        title: {
          display: true,
          text: 'Intensity vs. Time'
        }
      },
      scales: {
        x: {
          title: {
            display: true,
            text: 'Time (seconds)'
          },
          ticks: {
            beginAtZero: true,
            stepSize: 1,
            max: sumDurations // Set the max value to the sum of durations
          }
        },
        y: {
          title: {
            display: true,
            text: 'Intensity'
          },
          ticks: {
            beginAtZero: true,
            max: 10
          }
        }
      }
    }
  });

  // Set a fixed height for the chart container
  var chartContainer = document.getElementById('graphCanvas').parentNode;
  chartContainer.style.height = '400px'; // Adjust the height as needed
});
