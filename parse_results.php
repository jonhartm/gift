<?php
function parse_results(&$saved_results, $submit_results, $parsed_questions) {
  // Match the submitted data to the questions
  // T/F & short answer are straightforward
  // MC/MA the answers have to be matched from the question
  if (!$saved_results) {
    // Create a new saved results variable
    $saved_results = create_blank_results($parsed_questions);
  }

  foreach ($submit_results['submit'] as $q_code => $a_code) {
    if ($q_code != "PHPSESSID") { // ignore the session ID
      // roll through the parsed questions to find the matching question
      $question_index = substr($q_code,0,1)-1; //
      $current_question = $parsed_questions[$question_index];

      if ($current_question->type == "true_false_question" || // T/F and SA submits are straightforward
        $current_question->type == "short_answer_question") {
          update_results($saved_results, $q_code, $a_code);
      } elseif ($current_question->type == "multiple_choice_question") {
        // Find the response text for this question in the parsed questions
        foreach ($current_question->parsed_answer as $parsed_answer) {
          if ($parsed_answer[3] == $a_code) { // parsed_answer[3] is the answer code
            update_results($saved_results, $q_code, $parsed_answer[1]);
          }
        }
      } elseif ($current_question->type == "multiple_answers_question") {
        // the q_code for MA questions is actually the code for the matching answer in $parsed_questions
        foreach ($current_question->parsed_answer as $parsed_answer) {
          if ($parsed_answer[3] == $q_code) {
            update_results($saved_results, $current_question->code, $parsed_answer[1]);
          }
        }
      } else {
        // There are only four question types at the moment
        throw new Exception("Unknown question type");
      }
    }
  }
}

function create_blank_results($parsed_questions) {
  $new_results = array();
  foreach ($parsed_questions as $question) {
    // find the correct answer in the question array
    if ($question->type == 'true_false_question') {
      $correct_answer = array($question->answer); // T/F questions just state it
      // TODO - I would just used the following loop for T/F as well, but it appears "parsed_answer" isn't actually the answer to the question?
    } elseif ($question->type == 'multiple_choice_question' ||
      $question->type == 'multiple_answers_question' ||
      $question->type == 'short_answer_question') {
      $correct_answer = array();
      // the correct answer is the parsed answer where the first value in the array is true
      foreach ($question->parsed_answer as $possible_answer) {
        if ($possible_answer[0]) {
          array_push($correct_answer, $possible_answer[1]);
        }
      }
    } else {
      // Any other question types can be added here
      throw new Exception("Unknown question type");
    }
    // append this results array using the question code as the key
    $new_results[$question->code] = array(
      'correct_answer' => $correct_answer,
      'responses' => array()
    );
  }

  return $new_results;
}

function update_results(&$saved_results, $q_code, $submitted_answer) {
  // roll through each of the saved results to see what question this matches
  foreach ($saved_results as $question_results_code => &$question_results) {
    if ($question_results_code == $q_code) { // we found a match
      if (isset($question_results['responses'][$submitted_answer])) {
        $question_results['responses'][$submitted_answer] += 1; // add one to the entry that matches this one
      } else {
        $question_results['responses'][$submitted_answer] = 1; // or if there isn't an entry , create one with the value 1
      }
    }
  }
}
