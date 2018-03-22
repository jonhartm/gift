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
<div id="chartWrapper" style="width: 90%; margin: auto;"></div>
<?php

$OUTPUT->footerStart();
?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.4.0/Chart.min.js"></script>
<script src="create_chart.js"></script>
<script>
// Draw a chart for each of the results in the results data
function drawCharts() {
  $.getJSON("<?= addSession('results_data.php') ?>", function(resultData) {
    if (!resultData){
      $("#chartWrapper").html("No results have been recorded..."); // In the event there are no results and we try to check
    } else {
      // Compile the handlebars template
      var source = document.getElementById("accordion-template").innerHTML;
      var template = Handlebars.compile(source);
      for (var q_code in resultData) {
        var div_title = resultData[q_code]['name'] + ": " + resultData[q_code]['text']; // The title for each accordion is the question title and text
        $("#chartWrapper").append(template({title:div_title , body:"testBody", canvas_id:q_code})); // append the handlebars template to the chart wrapper
        create_chart(q_code, resultData[q_code]);
      }
      // Function that creates the accordion behavior
      $(function() {
        $(".accordion").accordion({
          active:false,
          collapsible:true
        });
      });
    }
  });
}

$(document).ready( drawCharts() );
</script>

<!-- handlebars template for the accordion -->
<script id="accordion-template" type="text/x-handlebars-template">
  <div class="accordion">
    <h3>{{title}}</h3>
    <div>
      <!-- <p>{{body}}</p> -->
      <canvas class='chart' id={{canvas_id}}></canvas>
    </div>
  </div>
</script>

<?php
$OUTPUT->footerEnd();
