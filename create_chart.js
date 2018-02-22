function create_chart(canvasID, results) {
  var ctx = document.getElementById(canvasID).getContext('2d');
  ctx.canvas.width = 700;
  ctx.canvas.height = 100;

  console.log(results);

  result_labels = [];
  result_data = [];
  result_bg_color = [];
  result_border_color = [];

  for (var result in results['responses']) {
    result_labels.push(result); // the response that was made
    result_data.push(results['responses'][result]); // the number of responses

    // If it's a correct answer, make the bar green
    if (results['correct_answer'].indexOf(result) != -1) {
      console.log(result + " is green");
      result_bg_color.push('rgba(0, 255, 0, 0.2)');
      result_border_color.push('rgba(0, 255, 0, 0.5)');
    } else if (results['correct_answer'].indexOf("no answer") != -1) {
      console.log(result + " is gray");
      result_bg_color.push('rgba(255, 255, 255, 0.2)');
      result_border_color.push('rgba(255, 255, 255, 0.5)');
    } else {
      console.log(result + " is red");
      result_bg_color.push('rgba(255, 0, 0, 0.2)');
      result_border_color.push('rgba(255, 0, 0, 0.5)');
    }
  }

  var chart = new Chart(ctx, {
    // The type of chart we want to create
    type: 'horizontalBar',

    // The data for our dataset
    data: {
      labels: result_labels,
      datasets: [{
        data: result_data,
        backgroundColor: result_bg_color,
        borderColor: result_border_color,
        borderWidth: 5,
      }]
    },

    // Configuration options go here
    options: {
      responsive: true,
      title: {
        display: true,
        text: 'Question 1: 1+1=2'
      },
      legend: {
        display: false,
      },
      scales: {
        xAxes: [{
          ticks: {
            beginAtZero:true,
            stepSize: 1,
          }
        }]
      }
    }
  });
}
