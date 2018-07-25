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

echo("<select id='question_select'>");
foreach ($manual_graded_questions as $code => $title) {
    echo("<option value='$code'>$title</option>");
}
echo("</select>");

?>
<form method="post">
<ol id="quiz">
</ol>
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
    $.getJSON('<?= addSession("get_manual_grade_responses.php?id={$current_question["code"]}&text={$current_question["text"]}&type={$current_question["type"]}")?>', function(quiz_json){
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
          vals.submitted = quiz_json['responses'][i].json.submit[code]

          question.value = vals;
          question.review = true;
          $('#quiz').append(template(question));
        }
    }).fail( function() { alert('Unable to load quiz data'); } );
});
</script>
<?php
$OUTPUT->footerEnd();
