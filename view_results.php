<?php

require_once "../config.php";

use \Tsugi\Core\Cache;
use \Tsugi\Core\LTIX;

$LAUNCH = LTIX::requireData();
if ( ! $USER->instructor ) die("Requires instructor role");

$OUTPUT->header();
$OUTPUT->bodyStart();
$OUTPUT->topNav();

?>
<a href="index" class="btn btn-default">Return</a>
<div id="chartWrapper" style="width: 70%; margin: auto;"></div>
<?php

$OUTPUT->footerStart();
?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.4.0/Chart.min.js"></script>
<script>
// Draw a chart for each of the results in the results data
function drawCharts() {
  $.getJSON("<?= addSession('results_data.php') ?>", function(resultData) {
    for (var q_code in resultData) {
      $("#chartWrapper").append("<canvas id=" + q_code + "></canvas>");
      create_chart(q_code, resultData[q_code]);
    }
  });
}

function create_chart(canvasID, results) {
  var ctx = document.getElementById(canvasID).getContext('2d');

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
    } else if (result == "no answer") {
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
      responsiveAnimationDuration:1000, // fixes the canvas fighting with the frame and resizing constantly
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

$(document).ready( drawCharts() );
</script>
<?php
$OUTPUT->footerEnd();
