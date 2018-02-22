<?php
require_once "../config.php";

use \Tsugi\Core\LTIX;

$LTI = LTIX::session_start();

echo json_encode($LINK->getJsonKey('results'), JSON_PRETTY_PRINT);
