<?php

use \Tsugi\Core\Result;

// function htmlent_utf8($string) {
//     return htmlentities($string,ENT_QUOTES,$encoding = 'UTF-8');
// }

function curPageURL() {
    $pageURL = (!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] != "on")
             ? 'http'
             : 'https';
    $pageURL .= "://";
    $pageURL .= $_SERVER['HTTP_HOST'];
    //$pageURL .= $_SERVER['REQUEST_URI'];
    $pageURL .= $_SERVER['PHP_SELF'];
    return $pageURL;
}

function var_dump_pre($variable, $print=true) {
    ob_start();
    var_dump($variable);
    $result = ob_get_clean();
    if ( $print ) print htmlent_utf8($result);
    return $result;
}

function libxml_display_error($error)
{
    $return = "<br/>\n";
    switch ($error->level) {
    case LIBXML_ERR_WARNING:
        $return .= "<b>Warning $error->code</b>: ";
        break;
    case LIBXML_ERR_ERROR:
        $return .= "<b>Error $error->code</b>: ";
        break;
    case LIBXML_ERR_FATAL:
        $return .= "<b>Fatal Error $error->code</b>: ";
        break;
    }
    $return .= trim($error->message);
    if ($error->file) {
        $return .= " in <b>$error->file</b>";
    }
    $return .= " on line <b>$error->line</b>\n";

    return $return;
}

function getJSONforResult($result_id)
{
    global $CFG, $PDOX;

    $stmt = $PDOX->queryDie(
        "SELECT json FROM {$CFG->dbprefix}lti_result
            WHERE result_id = :RID",
        array(':RID' => $result_id)
    );
    $row = $stmt->fetch(\PDO::FETCH_ASSOC);
    return json_decode($row['json']);
}

function setJSONforResult($json, $result_id)
{
    global $CFG, $PDOX;

    $stmt = $PDOX->queryDie(
        "UPDATE {$CFG->dbprefix}lti_result SET json = :json, updated_at = NOW()
            WHERE result_id = :RID",
        array(
            ':json' => $json,
            ':RID' => $result_id)
    );
}

function setNoteforResult($note, $result_id)
{
    global $CFG, $PDOX;

    $stmt = $PDOX->queryDie(
        "UPDATE {$CFG->dbprefix}lti_result SET note = :note, updated_at = NOW()
            WHERE result_id = :RID",
        array(
            ':note' => $note,
            ':RID' => $result_id)
    );
}

function printJSON($json)
{
  echo("<pre>\n");
  echo(htmlentities(json_encode($json, JSON_PRETTY_PRINT)));
  echo("\n</pre>\n");
}
