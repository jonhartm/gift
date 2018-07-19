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
// QUESTION: Huh? why does this work without calling make_quiz?
//$quiz = make_quiz($_SESSION['gift_submit'], $questions, $errors);

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
<script>
TEMPLATES = [];
$(document).ready(function(){
    $.getJSON('<?= addSession('quiz.php')?>', function(quiz) {
        window.console && console.log(quiz);
        for(var i=0; i<quiz.questions.length; i++) {
            question = quiz.questions[i];
            type = question.type;

            // if question.value is an object, it may have been manually graded.
            // check to see if the score is a one in order to set question.correct.
            if (typeof(question.value) == "object") {
                question.correct = question.value.scored == "1";
            }

            // we're in grade-detail, so go ahead and add buttons in the template.
            question.review = true;

            console.log(type);
            if ( TEMPLATES[type] ) {
                template = TEMPLATES[type];
            } else {
                source  = $('#'+type).html();
                if ( source == undefined ) {
                    window.console && console.log("Did not find template for question type="+type);
                    continue;
                }
                template = Handlebars.compile(source);
                TEMPLATES[type] = template;
            }
            $('#quiz').append(template(question));


        }
    }).fail( function() { alert('Unable to load quiz data'); } );
});
</script>

<?php
$OUTPUT->footerEnd();
