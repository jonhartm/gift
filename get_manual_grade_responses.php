<?php
require_once "../config.php";

use \Tsugi\Core\LTIX;

header("Content-type:application/json");

$LTI = LTIX::session_start();

// Get the JSON of all results that are flagged as "Manual Grade Needed"
$results = $PDOX->allRowsDie(
    "SELECT result_id, json FROM `lti_result`
        WHERE link_id = :LI AND note = 'Manual Grade Needed'",
    array(':LI' => $LINK->id)
);

// decode the JSON for each result
for ($i=0; $i < sizeof($results); $i++) {
    $results[$i]['json'] = json_decode($results[$i]['json']);
    $response = array();
    foreach ($results[$i]['json']->submit as $key => $value) {
        if (substr($key, 0, 11) === $_GET['id']) {
            $response[$key] = $value;
        }
    }
    $results[$i]['json']->submit = $response;
}

$return['responses'] = $results;
$return['question_code'] = $_GET['id'];
$return['question_text'] = $_GET['text'];
$return['question_type'] = $_GET['type'];


echo json_encode($return, JSON_PRETTY_PRINT);