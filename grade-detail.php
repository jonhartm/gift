<?php
require_once "../config.php";
require_once "parse.php";
require_once "util.php";

use \Tsugi\Core\LTIX;
use \Tsugi\Grades\GradeUtil;

$LAUNCH = LTIX::requireData();

// Get the user's grade data also checks session
$row = GradeUtil::gradeLoad($_REQUEST['user_id']);

if ( count($_POST) > 0 ) {
    // create a new result data array from the POST data
    $result_data = array("when" => time(), "tries" => 9999, "submit" => $_POST);

    // send that result data to the JSON field in this student's row
    setJSONforResult(json_encode($result_data), $row['result_id']);

    // parse the gift and get a grade for this quiz
    $gift = $LINK->getJson();
    $questions = array();
    $errors = array();
    parse_gift($gift, $questions, $errors);
    $_SESSION['gift_submit'] = $_POST;
    $quiz = make_quiz($_POST, $questions, $errors);

    // Update the grade and confirm the change via flash message
    $gradetosend = $quiz['score']*1.0;
    $_SESSION['success'] = "Student grade updated. New grade: ".$gradetosend;

    // Use LTIX to send the grade back to the LMS.
    $debug_log = array();
    $RESULT->gradeSend($gradetosend, array("result_id" => $row['result_id']), $debug_log);
    $_SESSION['debug_log'] = $debug_log;

    header( 'Location: '.addSession('grade-detail.php?user_id='.$row['user_id']) ) ;
    return;
}


// View
$OUTPUT->header();
$OUTPUT->bodyStart();
$OUTPUT->flashMessages();

// Show the basic info for this user
GradeUtil::gradeShowInfo($row);

$json = json_decode($row['json']);

// Load the quiz
$gift = $LINK->getJson();
$questions = array();
$errors = array();
parse_gift($gift, $questions, $errors);

$_SESSION['gift_submit'] = (array)$json->submit;

?>
<p>Submitted Quiz</p>
<form method="post">
<ol id="quiz">
</ol>
<input type="submit" class="btn btn-default" value="Submit Modifications">
</form>
<br>
<?php

// Unique detail
if ( is_object($json) ) {
    echo("<p>Raw JSON:</p>\n");
    echo("<pre>\n");
    echo(htmlentities(json_encode($json, JSON_PRETTY_PRINT)));
    echo("\n</pre>\n");
}

$OUTPUT->footerStart();

require_once('templates.php');

?>
<script type="text/javascript" src="js/grade_detail.js"></script>
<script type="text/javascript" src="js/quiz_from_template.js"></script>
<script>
TEMPLATES = [];
$(document).ready(function(){
    $.getJSON('<?= addSession('quiz.php')?>', function(quiz_json){
        make_quiz_from_template(quiz_json, true);
    }).fail( function() { alert('Unable to load quiz data'); } );
});
</script>

<?php
$OUTPUT->footerEnd();
