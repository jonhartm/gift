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
    // get the result submitted by the student so we can save some of the info
    $student_old_result = getJSONforResult($row['result_id']);

    // create a new result data array from the POST data
    $result_data = array(
      "when" => $student_old_result->when,
      "tries" => $student_old_result->tries,
      "submit" => $_POST,
      "instr_review_when" => time()
    );

    // parse the gift and get a grade for this quiz
    $gift = $LINK->getJson();
    $questions = array();
    $errors = array();
    parse_gift($gift, $questions, $errors);
    $_SESSION['gift_submit'] = $_POST;
    $quiz = make_quiz($_POST, $questions, $errors);

    // send result data to the JSON field in this student's row
    setJSONforResult(json_encode($result_data), $row['result_id']);

    // Update the note field if there's still a manual grade needed or not
    if (!isset($quiz['manual_grade_needed'])) {
        setNoteforResult(NULL, $row['result_id']);
    } else {
        setNoteforResult("Manual Grade Needed", $row['result_id']);
    }

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
