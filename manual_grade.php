<?php
require_once "../config.php";
require_once "parse.php";

\Tsugi\Core\LTIX::getConnection();

use \Tsugi\Core\LTIX;

$LAUNCH = LTIX::requireData();
$p = $CFG->dbprefix;

// View
$OUTPUT->header();
$OUTPUT->bodyStart();


$OUTPUT->footerStart();

$OUTPUT->footerEnd();
