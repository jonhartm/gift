// Create a single chart from data recieved in results_data.php
function create_chart(canvasID, results) {
  var ctx = document.getElementById(canvasID).getContext('2d');

  // not the actual h/w of the chart, but sets the aspect ratio
  ctx.canvas.width = 400;
  ctx.canvas.height = 100;

  // convert the results object into a sortable array like [[k,v][k,v]]
  var sortable = [];
  for (var r in results['responses']) {
    var sum = 0
    for (var attempt in results['responses'][r]) { // since the responses are stored in an array indexed by attempt, for now just add up all the values in the array
      sum+=results['responses'][r][attempt];
    }
    sortable.push([r, sum]);
  }

  // reverse the array after sorting it by the value
  sortable.sort(function(a, b) {
      return a[1] - b[1];
  }).reverse();

  result_labels = [];
  result_data = [];
  result_bg_color = [];
  result_border_color = [];

  for (var result in sortable.slice(0,15)) {
    result_labels.push(sortable[result][0]); // the response that was made
    result_data.push(sortable[result][1]); // the number of responses

    if (results['correct_answer'].indexOf(sortable[result][0]) != -1) {
      // If it's a correct answer, make the bar green
      result_bg_color.push('rgba(0, 255, 0, 0.2)');
      result_border_color.push('rgba(0, 255, 0, 0.5)');
    } else if (sortable[result][0] == "no answer") {
      // If it's a no answer, make the bar gray
      result_bg_color.push('rgba(200, 200, 200, 0.2)');
      result_border_color.push('rgba(200, 200, 200, 0.5)');
    } else {
      // If it's a wrong answer, make the bar red
      result_bg_color.push('rgba(255, 0, 0, 0.2)');
      result_border_color.push('rgba(255, 0, 0, 0.5)');
    }
  }

  var chart = new Chart(ctx, {
    // The type of chart we want to create
    type: 'bar',

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
        text: results['name'] + ": " + results['text']
      },
      legend: {
        display: false,
      },
      scales: {
        yAxes: [{
          ticks: {
            suggestedMin: 0,
            beginAtZero:true,
          }
        }]
      }
    }
  });
}

function create_overall_chart(canvasID, results) {
  var data_labels = [];
  var correct_data = [];
  var incorrect_data = [];
  var no_answer_data = [];

  var question_index = 0;
  for (questionID in results) {
    correct_data.push(0);
    incorrect_data.push(0);
    no_answer_data.push(0);

    correct_data[question_index] += results[questionID]['totals'][0][0];
    incorrect_data[question_index] += results[questionID]['totals'][0][1];
    no_answer_data[question_index] += results[questionID]['totals'][0][2];

    var average_score = 100 * (
    correct_data[question_index]/
      (correct_data[question_index]+
      incorrect_data[question_index]+
      no_answer_data[question_index]));

    data_labels.push("Q" + (question_index+1) + " ("+ average_score + "%)");
    question_index++;
  }
  var ctx = document.getElementById(canvasID).getContext("2d");

  var data = {
    labels: data_labels,
    datasets: [
        {
            label: "Correct",
            backgroundColor: "rgba(0, 255, 0, 0.2)",
            borderColor: "rgba(0, 255, 0, 0.5)",
            borderWidth: 5,
            data: correct_data
        },
        {
            label: "Incorrect",
            backgroundColor: "rgba(255, 0, 0, 0.2)",
            borderColor: "rgba(255, 0, 0, 0.5)",
            borderWidth: 5,
            data: incorrect_data
        },
        {
            label: "No Answer",
            backgroundColor: "rgba(200, 200, 200, 0.2)",
            borderColor: "rgba(200, 200, 200, 0.5)",
            borderWidth: 5,
            data: no_answer_data
        }
    ]
};

var myBarChart = new Chart(ctx, {
    type: 'horizontalBar',
    data: data,
    options: {
        title: {
            display: true,
            text: 'Overall Results By Question For the First Submission'
        },
        barValueSpacing: 20,
        scales: {
            xAxes: [{
                ticks: {
                    min: 0,
                    stepSize: 1,
                }
            }]
        }
    }
  });
}
