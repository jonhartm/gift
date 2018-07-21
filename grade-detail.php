<?php
require_once "../config.php";
require_once "parse.php";

use \Tsugi\Core\LTIX;
use \Tsugi\Core\Result;
use \Tsugi\Grades\GradeUtil;

$LAUNCH = LTIX::requireData();

// Get the user's grade data also checks session
$row = GradeUtil::gradeLoad($_REQUEST['user_id']);

if ( count($_POST) > 0 ) {
    // Make a new student result so we can store the modified json data
    $student_result = new \Tsugi\Core\Result();
    $student_result->launch = $TSUGI_LAUNCH;
    $student_result->id = $row['result_id'];

    $result_data = array("when" => time(), "tries" => 1, "submit" => $_POST);

    $student_result->setJson(json_encode($result_data));

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
<input type="submit" value="Submit Modifications">
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
