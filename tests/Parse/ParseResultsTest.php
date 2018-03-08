<?php

require_once "parse.php";
require_once "../../vendor/tsugi/lib/src/Util/Mersenne_Twister.php";
require_once "parse_results.php";

class ParseResults extends PHPUnit_Framework_TestCase
{
    public function test_ParseResults_notEmpty() {
      $gift = file_get_contents('.\tests\Parse\good_gift.gift');
      $questions = false;
      $errors = [];
      parse_gift($gift, $questions, $errors); # parse the gift

      $submit = array( // a submit for a perfect score
        'PHPSESSID'=>'baa5640b2e05c0af6dfc92f76e423cb7',
        '1:0cfae3833'=>'T',
        '2:11510fc8c'=>'2:1:92b09c',
        '3:1:92b09c'=>'true',
        '3:2:d0a389'=>'true',
        '4:3243f1f11'=>'2'
      );
      $_SESSION['gift_submit'] = $submit;
      $results = make_quiz($_SESSION['gift_submit'], $questions, $errors);

      $overalls = array(
        "1:0cfae3833"=> array(
          "correct_answer"=>array("T"),
          "responses" => array(
            "T"=>8,
            "F"=>1
          )
        ),
        "2:11510fc8c"=> array(
          "correct_answer"=>array("Right"),
          "responses" => array(
            "Right"=>6,
            "Wrong"=>2,
            "Incorrect"=>1,
            "Not right"=>0
          )
        ),
        "3:ae43574bc"=> array(
          "correct_answer"=> array("Right", "Correct"),
          "responses" => array(
            "Right"=>8,
            "Correct"=>5,
            "Wrong"=>3,
            "Incorrect"=>6
          )
        ),
        "4:3243f1f11"=> array(
          "correct_answer"=> array("2", "two"),
          "responses" => array(
            "2"=>4,
            "two"=>3,
            "4"=>1,
            "tow"=>1
          )
        )
      );
      $overalls = parse_results($overalls, $results, $questions);

      $this->assertEquals($overalls["1:0cfae3833"]["responses"], array("T"=>9, "F"=>1));
      $this->assertEquals($overalls["2:11510fc8c"]["responses"], array("Right"=>7, "Wrong"=>2, "Incorrect"=>1, "Not right"=>0));
      $this->assertEquals($overalls["3:ae43574bc"]["responses"], array("Right"=>9, "Correct"=>6, "Wrong"=>3, "Incorrect"=>6));
      $this->assertEquals($overalls["4:3243f1f11"]["responses"], array("2"=>5, "two"=>3, "4"=>1, "tow"=>1));
    }

    public function test_ParseResults_Empty() {
      $gift = file_get_contents('.\tests\Parse\good_gift.gift');
      $questions = false;
      $errors = [];
      parse_gift($gift, $questions, $errors); # parse the gift

      $submit = array( // a submit for a perfect score
        'PHPSESSID'=>'baa5640b2e05c0af6dfc92f76e423cb7',
        '1:0cfae3833'=>'T',
        '2:11510fc8c'=>'2:1:92b09c',
        '3:1:92b09c'=>'true',
        '3:2:d0a389'=>'true',
        '4:3243f1f11'=>'2'
      );
      $_SESSION['gift_submit'] = $submit;
      $results = make_quiz($_SESSION['gift_submit'], $questions, $errors);

      $overalls = false;

      $overalls = parse_results($overalls, $results, $questions);

      // Check that the $overalls were created correctly
      $this->assertEquals($overalls['1:0cfae3833']["correct_answer"], array("T"));
      $this->assertEquals($overalls['2:11510fc8c']["correct_answer"], array("Right"));
      $this->assertEquals($overalls['3:ae43574bc']["correct_answer"], array("Right", "Correct"));
      $this->assertEquals($overalls['4:3243f1f11']["correct_answer"], array("two", "2"));

      // Check that the answers were added correctly
      $this->assertEquals($overalls["1:0cfae3833"]["responses"], array("T"=>1));
      $this->assertEquals($overalls["2:11510fc8c"]["responses"], array("Right"=>1));
      $this->assertEquals($overalls["3:ae43574bc"]["responses"], array("Right"=>1, "Correct"=>1));
      $this->assertEquals($overalls["4:3243f1f11"]["responses"], array("2"=>1));
    }

    public function test_ParseResults_IncompleteSubmission() {
      $gift = file_get_contents('.\tests\Parse\good_gift.gift');
      $questions = false;
      $errors = [];
      parse_gift($gift, $questions, $errors); # parse the gift

      $submit = array( // the user did not answer any questions
        'PHPSESSID'=>'baa5640b2e05c0af6dfc92f76e423cb7',
        '4:3243f1f11'=>''
      );
      $_SESSION['gift_submit'] = $submit;
      $results = make_quiz($_SESSION['gift_submit'], $questions, $errors);

      $overalls = false;

      $overalls = parse_results($overalls, $results, $questions);

      // Check that the answers were added correctly
      $this->assertEquals($overalls["1:0cfae3833"]["responses"], array("no answer"=>1));
      $this->assertEquals($overalls["2:11510fc8c"]["responses"], array("no answer"=>1));
      $this->assertEquals($overalls["3:ae43574bc"]["responses"], array("no answer"=>1));
      $this->assertEquals($overalls["4:3243f1f11"]["responses"], array("no answer"=>1));

      $submit = array( // the next user did not answer the multiple choice, but did answer the others, just got them wrong
        'PHPSESSID'=>'baa5640b2e05c0af6dfc92f76e423cb7',
        '1:0cfae3833'=>'F',
        '3:1:92b09c'=>'true',
        '4:3243f1f11'=>'threeve'
      );
      $_SESSION['gift_submit'] = $submit;
      $results = make_quiz($_SESSION['gift_submit'], $questions, $errors);

      $overalls = parse_results($overalls, $results, $questions);

      // Check that the answers were added correctly
      $this->assertEquals($overalls["1:0cfae3833"]["responses"], array('no answer'=>1, 'F'=>1));
      $this->assertEquals($overalls["2:11510fc8c"]["responses"], array("no answer"=>2));
      $this->assertEquals($overalls["3:ae43574bc"]["responses"], array("no answer"=>1, "Right"=>1));
      $this->assertEquals($overalls["4:3243f1f11"]["responses"], array("no answer"=>1, "threeve"=>1));
    }

    public function test_NewQuestionAddedNotInResults() {
      $gift = file_get_contents('.\tests\Parse\good_gift.gift');
      $questions = false;
      $errors = [];
      parse_gift($gift, $questions, $errors); # parse the gift

      $submit = array( // a submit for a perfect score
        'PHPSESSID'=>'baa5640b2e05c0af6dfc92f76e423cb7',
        '1:0cfae3833'=>'T',
        '2:11510fc8c'=>'2:1:92b09c',
        '3:1:92b09c'=>'true',
        '3:2:d0a389'=>'true',
        '4:3243f1f11'=>'2'
      );
      $_SESSION['gift_submit'] = $submit;
      $results = make_quiz($_SESSION['gift_submit'], $questions, $errors);

      $overalls = array(
        "1:0cfae3833"=> array(
          "correct_answer"=>array("T"),
          "responses" => array(
            "T"=>8,
            "F"=>1
          )
        ),
        "2:11510fc8c"=> array(
          "correct_answer"=>array("Right"),
          "responses" => array(
            "Right"=>6,
            "Wrong"=>2,
            "Incorrect"=>1,
            "Not right"=>0
          )
        ),
        "3:ae43574bc"=> array(
          "correct_answer"=> array("Right", "Correct"),
          "responses" => array(
            "Right"=>8,
            "Correct"=>5,
            "Wrong"=>3,
            "Incorrect"=>6
          )
        )
      );
      $overalls = parse_results($overalls, $results, $questions);

      $this->assertEquals($overalls['4:3243f1f11']["correct_answer"], array("two", "2"));
      $this->assertEquals($overalls["4:3243f1f11"]["responses"], array("2"=>1));
    }

    public function test_QuestionRemoved() {
      $gift = file_get_contents('.\tests\Parse\altered_gift.gift');
      $questions = false;
      $errors = [];
      parse_gift($gift, $questions, $errors); # parse the gift

      $submit = array( // a submit for a perfect score
        'PHPSESSID'=>'baa5640b2e05c0af6dfc92f76e423cb7',
        '1:0cfae3833'=>'T',
        '2:11510fc8c'=>'2:1:92b09c',
        '3:1:92b09c'=>'true',
        '3:2:d0a389'=>'true',
        '4:661a455e8'=>'new'
      );
      $_SESSION['gift_submit'] = $submit;
      $results = make_quiz($_SESSION['gift_submit'], $questions, $errors);

      $overalls = array(
        "1:0cfae3833"=> array(
          "correct_answer"=>array("T"),
          "responses" => array(
            "T"=>8,
            "F"=>1
          )
        ),
        "2:11510fc8c"=> array(
          "correct_answer"=>array("Right"),
          "responses" => array(
            "Right"=>6,
            "Wrong"=>2,
            "Incorrect"=>1,
            "Not right"=>0
          )
        ),
        "3:ae43574bc"=> array(
          "correct_answer"=> array("Right", "Correct"),
          "responses" => array(
            "Right"=>8,
            "Correct"=>5,
            "Wrong"=>3,
            "Incorrect"=>6
          )
        ),
        "4:3243f1f11"=> array(
          "correct_answer"=> array("2", "two"),
          "responses" => array(
            "2"=>4,
            "two"=>3,
            "4"=>1,
            "tow"=>1
          )
        )
      );
      $overalls = parse_results($overalls, $results, $questions);

      $this->assertArrayNotHasKey("4:3243f1f11", $overalls);
      $this->assertEquals($overalls['4:661a455e8']["correct_answer"], array("new"));
      $this->assertEquals($overalls['4:661a455e8']["responses"], array("new"=>1));

    }
}
