<?php
// $saved_results: The quiz results that are stored in the JSON at the moment. ("false" if none are present)
// $submit_results: The results from the user's submitted quiz. Returned from make_quiz()
// $parsed_questions: the parsed gift quiz. Returned from parse_gift()
function parse_results($saved_results, $submit_results, $parsed_questions, $attempt_number) {
  // Match the submitted data to the questions
  // T/F & short answer are straightforward
  // MC/MA the answers have to be matched from the question
  if (!$saved_results) {
    // Create a new saved results variable
    $saved_results = create_blank_results($parsed_questions);
  }

  // Check to see if the questions still match the results
  foreach ($parsed_questions as $q_data) {
    if (!array_key_exists($q_data->code, $saved_results)) { // If we don't find this question in the results...
      $saved_results[$q_data->code] = create_blank_entry($q_data); // add a new blank entry with this question's data
    }
  }

  // Check to see if any results are now for questions that aren't being asked
  foreach ($saved_results as $result_q_code => $value) {
    $found = false;
    foreach ($parsed_questions as $q_data) {
      if ($q_data->code == $result_q_code) {
        $found = true;
        break; // jump out early if we found a matching pair of keys
      }
    }
    if (!$found) {
      // we didn't find this question key, so dump the results for the question
      unset($saved_results[$result_q_code]);
    }
  }

  // make a quick array of question code => bool so we can see if any quesions weren't answered
  $answered_questions = array();
  foreach ($saved_results as $q_code => $responses) {
    $answered_questions[$q_code] = false;
  }

  foreach ($submit_results['submit'] as $q_code => $a_code) {
    if ($q_code != "PHPSESSID") { // ignore the session ID
      // roll through the parsed questions to find the matching question
      $question_index = substr($q_code,0,1)-1; //
      $current_question = $parsed_questions[$question_index];

      if ($current_question->type == "true_false_question" || // T/F and SA submits are straightforward
        $current_question->type == "short_answer_question") {
          if (strlen($a_code) == 0) {
            // Short answers still get submitted even if they're blank, just as a q_code with a value of ''.
            // If this is the case, insert a no answer here, since it won't get caught by the $answered_questions array later.
            update_results($saved_results, $q_code, "no answer", $attempt_number);
          } else {
            update_results($saved_results, $q_code, $a_code, $attempt_number);
          }
      } elseif ($current_question->type == "multiple_choice_question") {
        // Find the response text for this question in the parsed questions
        foreach ($current_question->parsed_answer as $parsed_answer) {
          if ($parsed_answer[3] == $a_code) { // parsed_answer[3] is the answer code
            update_results($saved_results, $q_code, $parsed_answer[1], $attempt_number);
          }
        }
      } elseif ($current_question->type == "multiple_answers_question") {
        // the q_code for MA questions is actually the code for the matching answer in $parsed_questions
        foreach ($current_question->parsed_answer as $parsed_answer) {
          if ($parsed_answer[3] == $q_code) {
            // because of the way the submit handles MA responses, we need to affirm that this question was answered here
            // $q_code will be something like 3:1:92b09c, which pertains to the selected answer, wheras the question code is actually 3:ae43574bc
            $answered_questions[$current_question->code] = true;
            update_results($saved_results, $current_question->code, $parsed_answer[1], $attempt_number);
          }
        }
      } else {
        // There are only four question types at the moment
        throw new Exception("Unknown question type");
      }
      // Mark this quesiton as answered
      $answered_questions[$q_code] = true;
    }
  }

  // Go through the answered questions array and mark any questions that weren't answered as "no answer"
  foreach ($answered_questions as $q_code => $answered) {
    if (!$answered) {
      update_results($saved_results, $q_code, "no answer", $attempt_number);
    }
  }

  // sort the results as we return them
  sort_all_results($saved_results);
  return $saved_results;
}

function sort_all_results(&$saved_results){
  //sort the results
  foreach ($saved_results as $q_code => $response) {
    // we need to sort the responses based on the sum of the number of responses
    uasort($saved_results[$q_code]['responses'], function ($a, $b) {
      if (array_sum($a)==array_sum($b)) return 0;
      return (array_sum($a) < array_sum($b))?1:-1;
    });
  }
}

// Gets the score for a particular question from a submit
function get_score_by_question($question_code, $submit_results) {
  foreach ($submit_results['questions'] as $question) {
    if ($question->code == $question_code) {
      return $question->score;
    }
  }
  return false;
}

// Used when we don't have a results json stored yet - create a new one based on the parsed questions
function create_blank_results($parsed_questions) {
  $new_results = array();
  foreach ($parsed_questions as $question) {
    // append this results array using the question code as the key
    $new_results[$question->code] = create_blank_entry($question);
  }
  return $new_results;
}

// Create a single blank entry for storing in the result array
// requres a single value from the $parsed_questions returned by parse_gift()
function create_blank_entry($question_data) {
  if ($question_data->type == 'true_false_question') {
    $correct_answer = array($question_data->answer); // T/F questions just state it
    // TODO - I would just used the following loop for T/F as well, but it appears "parsed_answer" isn't actually the answer to the question?
  } elseif ($question_data->type == 'multiple_choice_question' ||
    $question_data->type == 'multiple_answers_question' ||
    $question_data->type == 'short_answer_question') {
    $correct_answer = array();
    // the correct answer is the parsed answer where the first value in the array is true
    foreach ($question_data->parsed_answer as $possible_answer) {
      if ($possible_answer[0]) {
        array_push($correct_answer, $possible_answer[1]);
      }
    }
  } else {
    // Any other question types can be added here
    throw new Exception("Unknown question type");
  }

  return array(
      'name' => $question_data->name,
      'text' => $question_data->question,
      'correct_answer' => $correct_answer,
      'responses' => array()
  );
}

// the function that actually does work - adds the response to the saved results array in the proper location
function update_results(&$saved_results, $q_code, $submitted_answer, $attempt_number) {
  // roll through each of the saved results to see what question this matches
  foreach ($saved_results as $question_results_code => &$question_results) {
    if ($question_results_code == $q_code) { // we found a match
      if (isset($question_results['responses'][$submitted_answer][$attempt_number])) {
        $question_results['responses'][$submitted_answer][$attempt_number] += 1; // add one to the entry that matches this one
      } else {
        $question_results['responses'][$submitted_answer][$attempt_number] = 1; // or if there isn't an entry , create one with the value 1
      }
    }
  }
}
