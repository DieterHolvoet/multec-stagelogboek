<?php

use M1\Env\Parser;

include '../../vendor/autoload.php';
include '../../classes/StageLogboek/Entry.php';
include '../../classes/StageLogboek/Parser.php';
include '../../classes/StageLogboek/Session.php';

$env = Parser::parse(file_get_contents('../../.env'));
$session = new StageLogboek\Session($env['STAGELOGBOEK_USER'], $env['STAGELOGBOEK_PW']);

if (isset($_GET['date'])) {
    $entries = $session->getEntriesForDate(DateTime::createFromFormat('U', $_GET['date']));
} else {
    $entries = $session->getEntries();
}

header('Content-type: application/json');
echo json_encode($entries);