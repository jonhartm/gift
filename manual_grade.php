<?php
require_once "../config.php";
require_once "parse.php";
require_once "util.php";

\Tsugi\Core\LTIX::getConnection();

use \Tsugi\Core\LTIX;

$LAUNCH = LTIX::requireData();
if ( ! $USER->instructor ) die("Requires instructor role");
$p = $CFG->dbprefix;

// Parse the gift
$gift = $LINK->getJson();
$questions = array();
$errors = array();
parse_gift($gift, $questions, $errors);

// Pull out questions which might need to be manually graded
$manual_graded_questions = array();
foreach ($questions as $question) {
    if ($question->type == "essay_question") {
        array_push(
          $manual_graded_questions,
          array(
            "code" => $question->code,
            "text" => "{$question->name}-{$question->question}",
            "type" => $question->type
          )
        );
    }
}

// Get the JSON of all results that are flagged as "Manual Grade Needed"
$results = $PDOX->allRowsDie(
    "SELECT result_id, json FROM `lti_result`
        WHERE link_id = :LI AND note = 'Manual Grade Needed'",
    array(':LI' => $LINK->id)
);

// decode the JSON for each result
for ($i=0; $i < sizeof($results); $i++) {
    $results[$i]['json'] = json_decode($results[$i]['json']);
}

// If there is a post
if ( count($_POST) > 0 ) {
    // Pull out the scores and associate them in a new array like result_id => score
    $results = array();
    $question_code = False;
    foreach ($_POST as $key => $value ) {
        if ($key === "PHPSESSID") continue;

        // get the question code for later if we don't have it already
        if (!$question_code) {
            $question_code = explode("|", $key)[1]; // Get the part of the q_code after the "|"
            $question_code = explode("-", $question_code)[0]; // But before any "-score"
        }

        // the post key should always start like "result_id|question_code"
        $result_id = explode("|", $key)[0];

        // if the post key ends in "-score", it's a score value for this question and we should store with this result_id
        if (strpos($key, "-score") > 0) {
            $results[$result_id] = $value;
        }
    }

    // iterate through our results and update the grades for each
    foreach ($results as $result_id => $score) {
        // get the stored JSON for this student
        $student_result = getJSONforResult($result_id);
        // update that JSON to include the manual grade for this question
        $student_result->submit->{$question_code.'-score'} = $score;
        // set the JSON along with the updated manual grade
        setJSONforResult(json_encode($student_result), $result_id);

        // set the gift submit to this student's result json->submit
        $_SESSION['gift_submit'] = (array)$student_result->submit;
        // grade the quiz using the gift we parsed earlier
        $quiz = make_quiz($_SESSION['gift_submit'], $questions, $errors);

        // Update the grade and confirm the change via flash message
        $gradetosend = $quiz['score']*1.0;

        // Use LTIX to send the grade back to the LMS.
        $debug_log = array();
        $RESULT->gradeSend($gradetosend, array("result_id" => $result_id), $debug_log);
        $_SESSION['debug_log'] = $debug_log;
    }

    if (isset($_POST['submit_and_next'])) {
        // The user wants to move to the next question
        // increment the counter until we reach the question we're on
        $count = 0;
        foreach ($manual_graded_questions as $question) {
            $count++;
            if ($question['code'] == $question_code) {
                break;
            }
        }
        // the counter should be the index of the next manual question
        $_SESSION['question_code'] = $manual_graded_questions[$count]['code'];
    } else {
        // Save the current question code as a session variable so we can load the page correctly
        $_SESSION['question_code'] = $question_code;
    }
    header( 'Location: '.addSession('manual_grade.php') ) ;
}

// View
$OUTPUT->header();
$OUTPUT->bodyStart();

?>
<a href="grades.php" class="btn btn-primary">Return to All Grades</a>
<hr>
<div class="input-group">
  <select class='form-control' id='question_select'>
<?php
foreach ($manual_graded_questions as $question) {
    if (isset($_SESSION['question_code']) && $_SESSION['question_code']==$question["code"]) {
      // if this is the question that we just submitted, make sure it's selected
      // the quiz questions that are reviewed are based on which of these is selected
      echo("<option value='{$question["code"]}' selected>{$question["text"]}</option>");
    } else {
      echo("<option value='{$question["code"]}'>{$question["text"]}</option>");
    }
}
?>
  </select>
</div>
<br>
<form method="post">
<ol id="quiz">
</ol>
<input type="submit" class="btn btn-success" value="Submit Grades">
<button type="submit" class="btn btn-success" name="submit_and_next" id="btn_submit_and_next">
   Submit and Go To Next Question <i class="fa fa-arrow-right"></i>
</button>
</form>
<?php

$OUTPUT->footerStart();

require_once('templates.php');
?>
<script type="text/javascript" src="js/quiz_from_template.js"></script>
<script type="text/javascript" src="js/grade_detail.js"></script>
<script>
TEMPLATES = [];
$(document).ready(function(){
    make_quiz_review();

    $("#question_select").change(function() {
        // remove all of the children of the quiz list
        $('#quiz').empty();
        $('#quiz').text("Loading...");
        $('#quiz').empty();
        make_quiz_review()
    });
});

function make_quiz_review(quiz_json) {
    var question_select = $("#question_select option:selected");
    $.getJSON('<?= addSession("get_manual_grade_responses.php")?>',
        {
            id: question_select.val(),
            text: question_select.text(),
            type: 'essay_question' // TODO: this probably shouldn't be hardcoded.
        }, function(quiz_json) {
          var code = quiz_json['question_code'];
          var type = quiz_json['question_type'];
          var question_text = quiz_json['question_text']

          if ( TEMPLATES[type] ) {
              template = TEMPLATES[type];
          } else {
              source  = $('#'+type).html();
              if ( source == undefined ) {
                  window.console && console.log("Did not find template for question type="+type);
                  return;
              }
              template = Handlebars.compile(source);
              TEMPLATES[type] = template;
          }

          for (var i = 0; i < quiz_json.responses.length; i++) {
            var question = new Object();
            question.code = code;
            question.type = type;
            question.question = question_text;
            question.result_id = quiz_json['responses'][i].result_id;
            var vals = new Object();
            vals.submitted = quiz_json['responses'][i].json.submit[code];
            if (typeof quiz_json['responses'][i].json.submit[code+"-score"] !== "undefined") {
                question.scored = true;
                question.correct = (quiz_json['responses'][i].json.submit[code+"-score"]=="1");
                vals.score = quiz_json['responses'][i].json.submit[code+"-score"];
            }

            question.value = vals;
            question.review = true;
            $('#quiz').append(template(question));
          }
    }).fail( function() { alert('Unable to load quiz data'); } );

    // Hide the Submit and Next button if we've selected the last question
    if ($("#question_select").prop('selectedIndex') >= ($("#question_select option").length-1)) {
        $("#btn_submit_and_next").hide();
    } else {
        $("#btn_submit_and_next").show();
    }

}
</script>
<?php
$OUTPUT->footerEnd();
