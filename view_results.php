<?php

require_once "../config.php";

use \Tsugi\Core\Cache;
use \Tsugi\Core\LTIX;

$LAUNCH = LTIX::requireData();
if ( ! $USER->instructor ) die("Requires instructor role");

$OUTPUT->header();
?>
<style>
  .chart {
    margin: 75px 0px;
  }
</style>
<?php
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
    if (!resultData){
      $("#chartWrapper").html("No results have been recorded...");
    } else {
      for (var q_code in resultData) {
        $("#chartWrapper").append("<canvas class='chart' id=" + q_code + "></canvas>");
        create_chart(q_code, resultData[q_code]);
      }
    }
  });
}

function create_chart(canvasID, results) {
  var ctx = document.getElementById(canvasID).getContext('2d');

  // convert the results object into a sortable array like [[k,v][k,v]]
  var sortable = [];
  for (var r in results['responses']) {
      sortable.push([r, results['responses'][r]]);
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
    console.log(sortable[result]);
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

$(document).ready( drawCharts() );
</script>
<?php
$OUTPUT->footerEnd();
