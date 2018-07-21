<?php
require_once "parse.php";

class ConfigureTest extends PHPUnit_Framework_TestCase
{
  public function setUp() {
    $this->gift = "::Q1:: Who's buried in Grant's tomb?{=Ulysses S. Grant =Ulysses Grant}

    ::Q2:: Write a short biography of Thor Heyerdahl. {}

    ::Q3:: What is Kon Tiki. {}";

    // create a test post
    $this->submit_notGraded = Array
    (
        'PHPSESSID' => 'ca80ad1e7a9c9d9d71f742d563a99145',
        '1:329f32e6a' => "Ulysses Grant",
        '2:8d90b995a' => 'lorem ipsum',
        '3:94cbc8097' => 'dolor sit amet'
    );

    $this->submit_GradedWrong = Array
    (
        'PHPSESSID' => 'ca80ad1e7a9c9d9d71f742d563a99145',
        '1:329f32e6a' => "Ulysses Grant",
        '2:8d90b995a' => 'lorem ipsum',
        '2:8d90b995a-score' => "0",
        '3:94cbc8097' => 'dolor sit amet'
    );

    $this->submit_GradedCorrect = Array
    (
        'PHPSESSID' => 'ca80ad1e7a9c9d9d71f742d563a99145',
        '1:329f32e6a' => "Ulysses Grant",
        '2:8d90b995a' => 'lorem ipsum',
        '2:8d90b995a-score' => "1"
    );
  }

  public function testParseGiftEssay() {
    $questions = array();
    $errors = array();
    parse_gift($this->gift, $questions, $errors);

    $this->assertEquals(
      $questions[1]->type,
      "essay_question",
      "parse_gift should recognize the second question as an essay type");
  }

  public function testMakeQuizEssay() {
    $questions = array();
    $errors = array();
    parse_gift($this->gift, $questions, $errors);

    // Test a submission that has not been manually graded
    $_SESSION['gift_submit'] = $this->submit_notGraded;
    $parsed = make_quiz($_SESSION['gift_submit'], $questions, $errors);
    $this->assertTrue(
      isset($parsed["manual_grade_needed"]),
      "manual_grade_needed should exist when an essay question is missing a corresponding {code}-score submit");
    $this->assertEquals(
      $parsed["manual_grade_needed"],
      2,
      "manual_grade_needed should equal the number of questions awaiting a grade");
    $this->assertEquals(
      $parsed["score"],
      1,
      "make_quiz should skip an essay question when grading if it isn't manually graded");

    // Test a submission that has been manually graded and is incorrect
    $_SESSION['gift_submit'] = $this->submit_GradedWrong;
    $parsed = make_quiz($_SESSION['gift_submit'], $questions, $errors);
    $this->assertTrue(
      isset($parsed["manual_grade_needed"]),
      "manual_grade_needed should exist when an essay question is missing a corresponding {code}-score submit");
    $this->assertEquals(
      $parsed["manual_grade_needed"],
      1,
      "manual_grade_needed should equal the number of questions awaiting a grade");
    $this->assertEquals(
      $parsed["score"],
      0.5,
      "make_quiz should grade an essay question as a 0 if the cooresponding {code}-score is not equal to 1");

    // Test a submission that has been manually graded and is incorrect
    $_SESSION['gift_submit'] = $this->submit_GradedCorrect;
    $parsed = make_quiz($_SESSION['gift_submit'], $questions, $errors);
    $this->assertFalse(
      isset($parsed["manual_grade_needed"]),
      "manual_grade_needed should not be flagged when an essay question has a corresponding {code}-score submit");
    $this->assertEquals(
      $parsed["score"],
      1,
      "make_quiz should grade an essay question as a 1 if the cooresponding {code}-score is equal to 1");
  }
}
