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
<div id="chartWrapper" style="width: 80%; margin: auto;"></div>
<?php

$OUTPUT->footerStart();
?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.4.0/Chart.min.js"></script>
<script src="create_chart.js"></script>
<script>
function drawCharts() {
  $.getJSON("<?= addSession('results_data.php') ?>", function(resultData) {
    for (var q_code in resultData) {
      $("#chartWrapper").append("<canvas id=" + q_code + "></canvas>");
      create_chart(q_code, resultData[q_code]);
    }
  });
}

$(document).ready( drawCharts() );
</script>
<?php
$OUTPUT->footerEnd();
