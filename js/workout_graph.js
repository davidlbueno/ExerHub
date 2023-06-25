var ctx = document.getElementById('graphCanvas').getContext('2d');

var data = {
    labels: graphData.map(function(o) { return o.name; }),
    datasets: [{
        data: graphData.map(function(o) { return o.reps; }),
        backgroundColor: 'rgba(75, 192, 192, 0.2)',
        borderColor: 'rgba(75, 192, 192, 1)',
        borderWidth: 1
    }]
};

// Find the maximum duration value
var maxDuration = Math.max.apply(
  null,
  graphData.map(function (o) {
    return o.duration;
  })
);

// Round the x-axis maximum value to the next ten
var maxXAxis = Math.ceil(maxDuration / 10) * 10;

var data = {
  labels: graphData.map(function (o) {
    return o.name;
  }),
  datasets: [
    {
      data: graphData.map(function (o) {
        return o.reps;
      }),
      backgroundColor: 'rgba(75, 192, 192, 0.2)',
      borderColor: 'rgba(75, 192, 192, 1)',
      borderWidth: 1
    }
  ]
};

console.log(graphData);

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
        text: 'Reps', // Set the chart title
        font: {
          weight: 'bold'
        }
      }
    }
  };
  
  new Chart(ctx, {
    type: 'bar',
    data: data,
    options: options
  });
  
