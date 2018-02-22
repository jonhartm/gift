function create_chart(canvasID, results) {
  var ctx = document.getElementById(canvasID).getContext('2d');

  // set the realtive sizes for the canvas based on the number of different responses
  ctx.canvas.width = Object.keys(results['responses']).length * 10 + 50;
  ctx.canvas.height = Object.keys(results['responses']).length * 4;

  result_labels = [];
  result_data = [];
  result_bg_color = [];
  result_border_color = [];

  for (var result in results['responses']) {
    result_labels.push(result); // the response that was made
    result_data.push(results['responses'][result]); // the number of responses

    if (results['correct_answer'].indexOf(result) != -1) {
      // If it's a correct answer, make the bar green
      result_bg_color.push('rgba(0, 255, 0, 0.2)');
      result_border_color.push('rgba(0, 255, 0, 0.5)');
    } else if (results['correct_answer'].indexOf("no answer") != -1) {
      // If it's a no answer, make the bar gray
      result_bg_color.push('rgba(255, 255, 255, 0.2)');
      result_border_color.push('rgba(255, 255, 255, 0.5)');
    } else {
      // If it's a wrong answer, make the bar red
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
