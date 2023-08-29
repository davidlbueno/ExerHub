<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.7.2/Chart.min.js"></script>

<div class="chart-container" style="width: 80%; margin-left: 10%; margin-top: 5px;">
    <div>
        <div>
            <canvas id="myChart" width="50" height="10"></canvas>
        </div>
        <div>
            <button type="button" class="btn btn-default">Back</button>
        </div>
    </div>
</div>

<script>
    var ctx = document.getElementById("myChart");
    var myChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ["Mon", "Tue", "Wed", "Thur", "Fri", "Sat", "Sun"],
            datasets: [{
                label: 'Push',
                data: [12,,,5,,,12],
                backgroundColor: "rgba(255,153,0,0.4)"
            }, {
                label: 'Pull',
                data: [,29,,,24,,],
                backgroundColor: "rgba(0,153,255,0.4)"
            }, {
                label: 'Legs',
                data: [,,54,,,35,],
                backgroundColor: "rgba(255,0,0,0.4)"
            }]
        },
        options: {
          legend: {
            display: false,
          },
          scales: {
              xAxes: [{
                  stacked: true
              }],
              yAxes: [{
                  stacked: true,
                  ticks: {
                      beginAtZero:true
                  }
              }]
          }
        }
    });
  </script>
