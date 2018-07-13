<?php
require_once "../config.php";
require_once "parse.php";

use \Tsugi\Core\LTIX;
use \Tsugi\Grades\GradeUtil;

$LAUNCH = LTIX::requireData();

// Get the user's grade data also checks session
$row = GradeUtil::gradeLoad($_REQUEST['user_id']);

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
// TODO: Huh? why does this work without calling make_quiz?
//$quiz = make_quiz($_SESSION['gift_submit'], $questions, $errors);

?>
<p>Submitted Quiz</p>
<ol id="quiz">
</ol>
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
<script>
TEMPLATES = [];
$(document).ready(function(){
    $.getJSON('<?= addSession('quiz.php')?>', function(quiz) {
        window.console && console.log(quiz);
        for(var i=0; i<quiz.questions.length; i++) {
            question = quiz.questions[i];
            type = question.type;
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
