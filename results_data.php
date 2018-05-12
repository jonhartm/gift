<?php
require_once "../config.php";
require_once "parse.php";

use \Tsugi\Core\LTIX;

header('Content-Type: application/json');

$LTI = LTIX::session_start();

// Load the quiz
$gift = $LINK->getJsonKey('quiz');

// Load the results
$results = $LINK->getJsonKey('results');

$questions = false;
$errors = array("No questions found");
if ( strlen($gift) > 0 && $results) {
    $questions = array();
    $errors = array();
    parse_gift($gift, $questions, $errors);

    // Roll through the results and grab the question code for each
    foreach ($results as $q_code => $values) {
      // Match that question code to the parsed questions to grab the name and text for each
      foreach ($questions as $question) {
        if ($question->code == $q_code) {
          $results[$q_code]["name"] = $question->name;
          $results[$q_code]["text"] = $question->question;
        }
      }
    }
}

echo json_encode($results, JSON_PRETTY_PRINT);
