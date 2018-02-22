<?php

require_once "../config.php";

use \Tsugi\Core\Cache;
use \Tsugi\Core\LTIX;

// Sanity checks
$LAUNCH = LTIX::requireData();
if ( ! $USER->instructor ) die("Requires instructor role");

// Load in the current results from the JSON
$quiz_results = $LINK->getJsonKey('results');

$OUTPUT->header();
echo '<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.4.0/Chart.min.js"></script>';
$OUTPUT->bodyStart();
$OUTPUT->topNav();
$OUTPUT->flashMessages();
//
// echo '<pre>';
// var_dump($quiz_results);
// echo '</pre>';

?>
<input type=submit name=doCancel onclick="location='<?php echo(addSession('index.php'));?>'; return false;" value="Return"></p>
<canvas id="myChart"></canvas>
<script>
var ctx = document.getElementById('myChart').getContext('2d');
var chart = new Chart(ctx, {
    // The type of chart we want to create
    type: 'horizontalBar',

    // The data for our dataset
    data: {
      labels: ["T", "F"],
      datasets: [{
        data: [1, 2],
        backgroundColor: [
                'rgba(0, 255, 0, 0.2)',
                'rgba(255, 0, 0, 0.2)'
              ],
        borderColor: [
                'rgba(0, 255, 0, 0.5)',
                'rgba(255, 0, 0, 0.5)'
              ],
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
</script>

<?php

$OUTPUT->footer();
