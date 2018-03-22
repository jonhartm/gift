<?php

require_once "parse.php";
require_once "../../vendor/tsugi/lib/src/Util/Mersenne_Twister.php";
require_once "parse_results.php";

// Make sure the minor utility functions that help parse_results work as intended
class ParseResultsUtil extends PHPUnit_Framework_TestCase
{
  private function create_submit_results($gift, $submit) {
    $questions = false;
    $errors = [];
    parse_gift($gift, $questions, $errors);
    $_SESSION['gift_submit'] = $submit;
    return make_quiz($_SESSION['gift_submit'], $questions, $errors);
  }

  // get_score_by_question should return a 1 if the question was answered correctly, and a 0 if not
  public function test_GetScoreByQuestion() {
    $gift = file_get_contents('.\tests\Parse\good_gift.gift');
    $submit = array( // a submit for a perfect score
      'PHPSESSID'=>'baa5640b2e05c0af6dfc92f76e423cb7',
      '1:0cfae3833'=>'T',
      '2:11510fc8c'=>'2:1:92b09c',
      '3:1:92b09c'=>'true',
      '3:2:d0a389'=>'true',
      '4:3243f1f11'=>'2'
    );
    $submit_results = $this->create_submit_results($gift, $submit);

    $this->assertEquals(get_score_by_question('1:0cfae3833', $submit_results), 1);
    $this->assertEquals(get_score_by_question('2:11510fc8c', $submit_results), 1);
    $this->assertEquals(get_score_by_question('3:ae43574bc', $submit_results), 1);
    $this->assertEquals(get_score_by_question('4:3243f1f11', $submit_results), 1);

    $submit = array( // a submit for all wrong answers
      'PHPSESSID'=>'baa5640b2e05c0af6dfc92f76e423cb7',
      '1:0cfae3833'=>'F',
      '2:11510fc8c'=>'2:2:b35e5a',
      '3:2:d0a389'=>'true',
      '4:3243f1f11'=>'4'
    );
    $submit_results = $this->create_submit_results($gift, $submit);

    $this->assertEquals(get_score_by_question('1:0cfae3833', $submit_results), 0);
    $this->assertEquals(get_score_by_question('2:11510fc8c', $submit_results), 0);
    $this->assertEquals(get_score_by_question('3:ae43574bc', $submit_results), 0);
    $this->assertEquals(get_score_by_question('4:3243f1f11', $submit_results), 0);
  }

  // results should be sorted by the number of respondants who picked each one
  public function test_SortResults() {
    $sample_overalls = array(
      "1:0cfae3833"=> array(
        "correct_answer"=>array("T"),
        "responses" => array(
          "T"=>array(1,4),
          "F"=>array(9,1)
        )
      ),
      "2:11510fc8c"=> array(
        "correct_answer"=>array("Right"),
        "responses" => array(
          "Right"=>array(3,1),
          "Wrong"=>array(5,4),
          "Incorrect"=>array(5),
          "Not right"=>array(0,0,0,1)
        )
      )
    );
    sort_all_results($sample_overalls);

    $loc_F = array_search("F", array_keys($sample_overalls['1:0cfae3833']['responses']));
    $this->assertEquals($loc_F, 0);

    $loc_T = array_search("T", array_keys($sample_overalls['1:0cfae3833']['responses']));
    $this->assertEquals($loc_T, 1);
  }
}

// check that the parse_results function operates as it's expected to
class ParseResults extends PHPUnit_Framework_TestCase
{
  protected $sample_overalls;
  protected $perfect_submit;

  protected function setUp() {
      $this->sample_overalls = array(
        "1:0cfae3833"=> array(
          "correct_answer"=>array("T"),
          "responses" => array(
            "T"=>array(8),
            "F"=>array(1)
          ),
          "totals" => array(
            array(8,1,0)
          )
        ),
        "2:11510fc8c"=> array(
          "correct_answer"=>array("Right"),
          "responses" => array(
            "Right"=>array(6),
            "Wrong"=>array(2),
            "Incorrect"=>array(1),
            "Not right"=>array(0)
          ),
          "totals" => array(
            array(6,3,0)
          )
        ),
        "3:ae43574bc"=> array(
          "correct_answer"=> array("Right", "Correct"),
          "responses" => array(
            "Right"=>array(8),
            "Correct"=>array(5),
            "Wrong"=>array(3),
            "Incorrect"=>array(6)
          ),
          "totals" => array(
            array(4,5,0)
          )
        ),
        "4:3243f1f11"=> array(
          "correct_answer"=> array("2", "two"),
          "responses" => array(
            "2"=>array(4),
            "two"=>array(3),
            "4"=>array(1),
            "tow"=>array(1)
          ),
          "totals" => array(
            array(7,3,0)
          )
        ),
        "overall"=>array(
          "submits"=>[9],
          "cumulative_score"=>[26]
        )
      );

      $this->perfect_submit = array( // a submit for a perfect score
        'PHPSESSID'=>'baa5640b2e05c0af6dfc92f76e423cb7',
        '1:0cfae3833'=>'T',
        '2:11510fc8c'=>'2:1:92b09c',
        '3:1:92b09c'=>'true',
        '3:2:d0a389'=>'true',
        '4:3243f1f11'=>'2'
      );
    }

  private function create_overalls($gift, $submit, $overalls, $attempt=0) {
    $questions = false;
    $errors = [];
    parse_gift($gift, $questions, $errors);
    $_SESSION['gift_submit'] = $submit;
    $results = make_quiz($_SESSION['gift_submit'], $questions, $errors);
    return parse_results($overalls, $results, $questions, $attempt);
  }

  // Test that we can successfully add to an existing result
  public function test_notEmpty() {
    $gift = file_get_contents('.\tests\Parse\good_gift.gift');
    $overalls = $this->create_overalls($gift, $this->perfect_submit, $this->sample_overalls);

    $this->assertEquals($overalls["1:0cfae3833"]["responses"], array("T"=>array(9), "F"=>array(1)));
    $this->assertEquals($overalls["1:0cfae3833"]["totals"], array(array(9,1,0)));
    $this->assertEquals($overalls["2:11510fc8c"]["responses"], array("Right"=>array(7), "Wrong"=>array(2), "Incorrect"=>array(1), "Not right"=>array(0)));
    $this->assertEquals($overalls["2:11510fc8c"]["totals"], array(array(7,3,0)));
    $this->assertEquals($overalls["3:ae43574bc"]["responses"], array("Right"=>array(9), "Correct"=>array(6), "Wrong"=>array(3), "Incorrect"=>array(6)));
    $this->assertEquals($overalls["3:ae43574bc"]["totals"], array(array(5,5,0)));
    $this->assertEquals($overalls["4:3243f1f11"]["responses"], array("2"=>array(5), "two"=>array(3), "4"=>array(1), "tow"=>array(1)));
    $this->assertEquals($overalls["4:3243f1f11"]["totals"], array(array(8,3,0)));

    // a second submit by the same user
    $overalls = $this->create_overalls($gift, $this->perfect_submit, $overalls, 1);

    $this->assertEquals($overalls["1:0cfae3833"]["responses"], array("T"=>array(9, 1), "F"=>array(1)));
    $this->assertEquals($overalls["1:0cfae3833"]["totals"], array(array(9,1,0), array(1,0,0)));
    $this->assertEquals($overalls["2:11510fc8c"]["responses"], array("Right"=>array(7, 1), "Wrong"=>array(2), "Incorrect"=>array(1), "Not right"=>array(0)));
    $this->assertEquals($overalls["2:11510fc8c"]["totals"], array(array(7,3,0), array(1,0,0)));
    $this->assertEquals($overalls["3:ae43574bc"]["responses"], array("Right"=>array(9, 1), "Correct"=>array(6, 1), "Wrong"=>array(3), "Incorrect"=>array(6)));
    $this->assertEquals($overalls["3:ae43574bc"]["totals"], array(array(5,5,0), array(1,0,0)));
    $this->assertEquals($overalls["4:3243f1f11"]["responses"], array("2"=>array(5, 1), "two"=>array(3), "4"=>array(1), "tow"=>array(1)));
    $this->assertEquals($overalls["4:3243f1f11"]["totals"], array(array(8,3,0), array(1,0,0)));

    // a third submission - another user, different gift
    $submit = array( // a submit for all wrong answers
      'PHPSESSID'=>'baa5640b2e05c0af6dfc92f76e423cb7',
      '1:0cfae3833'=>'F',
      '2:11510fc8c'=>'2:2:b35e5a',
      '3:2:d0a389'=>'true',
      '4:3243f1f11'=>'4'
    );
    $overalls = $this->create_overalls($gift, $submit, $overalls);

    $this->assertEquals($overalls["1:0cfae3833"]["responses"], array("T"=>array(9, 1), "F"=>array(2)));
    $this->assertEquals($overalls["1:0cfae3833"]["totals"], array(array(9,2,0), array(1,0,0)));
    $this->assertEquals($overalls["2:11510fc8c"]["responses"], array("Right"=>array(7, 1), "Wrong"=>array(3), "Incorrect"=>array(1), "Not right"=>array(0)));
    $this->assertEquals($overalls["2:11510fc8c"]["totals"], array(array(7,4,0), array(1,0,0)));
    $this->assertEquals($overalls["3:ae43574bc"]["responses"], array("Right"=>array(9, 1), "Correct"=>array(7, 1), "Incorrect"=>array(6), "Wrong"=>array(3)));
    $this->assertEquals($overalls["3:ae43574bc"]["totals"], array(array(5,6,0), array(1,0,0)));
    $this->assertEquals($overalls["4:3243f1f11"]["responses"], array("2"=>array(5, 1), "two"=>array(3), "4"=>array(2), "tow"=>array(1)));
    $this->assertEquals($overalls["4:3243f1f11"]["totals"], array(array(8,4,0), array(1,0,0)));
  }

  // Test that we can create a good result from scratch
  public function test_Empty() {
    $gift = file_get_contents('.\tests\Parse\good_gift.gift');
    $overalls = false;

    $overalls = $this->create_overalls($gift, $this->perfect_submit, $overalls);

    // Check that the $overalls were created correctly
    $this->assertEquals($overalls['1:0cfae3833']["correct_answer"], array("T"));
    $this->assertEquals($overalls['2:11510fc8c']["correct_answer"], array("Right"));
    $this->assertEquals($overalls['3:ae43574bc']["correct_answer"], array("Right", "Correct"));
    $this->assertEquals($overalls['4:3243f1f11']["correct_answer"], array("two", "2"));


    // Check that the answers were added correctly
    $this->assertEquals($overalls["1:0cfae3833"]["responses"], array("T"=>array(1)));
    $this->assertEquals($overalls["2:11510fc8c"]["responses"], array("Right"=>array(1)));
    $this->assertEquals($overalls["3:ae43574bc"]["responses"], array("Right"=>array(1), "Correct"=>array(1)));
    $this->assertEquals($overalls["4:3243f1f11"]["responses"], array("2"=>array(1)));
    // Check that the score total for each question was calculated correctly
    // print_r($overalls["1:0cfae3833"]);
    $this->assertEquals($overalls["1:0cfae3833"]["totals"], array(array(1,0,0)));
  }

  // Test that we properly add "no answer" for submissions that are missing responses to questions
  public function test_IncompleteSubmission() {
    $gift = file_get_contents('.\tests\Parse\good_gift.gift');
    $submit = array( // the user did not answer any questions
      'PHPSESSID'=>'baa5640b2e05c0af6dfc92f76e423cb7',
      '4:3243f1f11'=>''
    );
    $overalls = false;

    $overalls = $this->create_overalls($gift, $submit, $overalls);

    // Check that the answers were added correctly
    $this->assertEquals($overalls["1:0cfae3833"]["responses"], array("no answer"=>array(1)));
    $this->assertEquals($overalls["1:0cfae3833"]["totals"], array(array(0,0,1)));
    $this->assertEquals($overalls["2:11510fc8c"]["responses"], array("no answer"=>array(1)));
    $this->assertEquals($overalls["2:11510fc8c"]["totals"], array(array(0,0,1)));
    $this->assertEquals($overalls["3:ae43574bc"]["responses"], array("no answer"=>array(1)));
    $this->assertEquals($overalls["3:ae43574bc"]["totals"], array(array(0,0,1)));
    $this->assertEquals($overalls["4:3243f1f11"]["responses"], array("no answer"=>array(1)));
    $this->assertEquals($overalls["4:3243f1f11"]["totals"], array(array(0,0,1)));

    $submit = array( // the next user did not answer the multiple choice, but did answer the others, just got them wrong
      'PHPSESSID'=>'baa5640b2e05c0af6dfc92f76e423cb7',
      '1:0cfae3833'=>'F',
      '3:1:92b09c'=>'true',
      '4:3243f1f11'=>'threeve'
    );

    $overalls = $this->create_overalls($gift, $submit, $overalls);

    // Check that the answers were added correctly
    $this->assertEquals($overalls["1:0cfae3833"]["responses"], array('no answer'=>array(1), 'F'=>array(1)));
    $this->assertEquals($overalls["2:11510fc8c"]["responses"], array("no answer"=>array(2)));
    $this->assertEquals($overalls["3:ae43574bc"]["responses"], array("no answer"=>array(1), "Right"=>array(1)));
    $this->assertEquals($overalls["4:3243f1f11"]["responses"], array("no answer"=>array(1), "threeve"=>array(1)));
  }

  // Test how we handle a result when a new question is added at a later time
  // (We should make a blank results entry for the new question)
  public function test_NewQuestionAddedNotInResults() {
    $gift = file_get_contents('.\tests\Parse\good_gift.gift');
    $overalls = $this->sample_overalls;
    unset($overalls['4:3243f1f11']); // remove the results for this question from the overalls
    $overalls = $this->create_overalls($gift, $this->perfect_submit, $overalls);

    $this->assertEquals($overalls['4:3243f1f11']["correct_answer"], array("two", "2"));
    $this->assertEquals($overalls["4:3243f1f11"]["responses"], array("2"=>array(1)));
  }

  // Test how we handle when a question is removed for which results are present
  // (The results should be removed as well)
  public function test_QuestionRemoved() {
    $gift = file_get_contents('.\tests\Parse\altered_gift.gift');
    $submit = $this->perfect_submit;
    unset($submit['4:3243f1f11']);
    $submit['4:661a455e8']='new';

    $overalls = $this->create_overalls($gift, $submit, $this->sample_overalls);

    $this->assertArrayNotHasKey("4:3243f1f11", $overalls);
    $this->assertEquals($overalls['4:661a455e8']["correct_answer"], array("new"));
    $this->assertEquals($overalls['4:661a455e8']["responses"], array("new"=>array(1)));
  }
}
