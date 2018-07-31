<?php
require_once "../config.php";
require_once "parse.php";
require_once "util.php";

\Tsugi\Core\LTIX::getConnection();

use \Tsugi\Core\LTIX;

$LAUNCH = LTIX::requireData();
$p = $CFG->dbprefix;

// Parse the gift
$gift = $LINK->getJson();
$questions = array();
$errors = array();
parse_gift($gift, $questions, $errors);

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
    header( 'Location: '.addSession('manual_grade.php') ) ;
}

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

$current_question = $manual_graded_questions[0];

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

// View
$OUTPUT->header();
$OUTPUT->bodyStart();

?>

<?php

echo("<select class='form-control' id='question_select'>");
foreach ($manual_graded_questions as $question) {
    echo("<option value='{$question["code"]}'>{$question["text"]}</option>");
}
echo("</select>");

?>
<br>
<form method="post">
<ol id="quiz">
</ol>
<input type="submit" class="btn btn-default" value="Submit Grades">
</form>
<?php

printJSON($current_question);

$OUTPUT->footerStart();

require_once('templates.php');
?>
<script type="text/javascript" src="js/quiz_from_template.js"></script>
<script type="text/javascript" src="js/grade_detail.js"></script>
<script>
TEMPLATES = [];
$(document).ready(function(){
    $.getJSON('<?= addSession("get_manual_grade_responses.php")?>',
        {
            id: '<?= $current_question["code"] ?>',
            text: '<?= $current_question["text"] ?>',
            type: '<?= $current_question["type"] ?>'
        }, function(quiz_json){
            make_review_quiz_from_template(quiz_json)
    }).fail( function() { alert('Unable to load quiz data'); } );
});

function make_review_quiz_from_template(quiz_json) {
  console.log(quiz_json);
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
}
</script>
<?php
$OUTPUT->footerEnd();
