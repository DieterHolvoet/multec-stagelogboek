<?php

include '../vendor/autoload.php';
include '../classes/StageLogboek/Session.php';
include '../classes/StageLogboek/Parser.php';
include '../classes/StageLogboek/Entry.php';
include '../classes/TimeTrackers/Harvest.php';

// Initialise settings
$env = M1\Env\Parser::parse(file_get_contents('../.env'));

// Initialise APIs
$session = new StageLogboek\Session($env['STAGELOGBOEK_USER'], $env['STAGELOGBOEK_PW']);
$harvest = new TimeTrackers\Harvest($env['HARVEST_USER'], $env['HARVEST_PW'], $env['HARVEST_ACCOUNT']);

// Initialise entries count
$entriesCount = 0;

// Get dates
$dates = explode(',', isset($_POST['dates']) ? $_POST['dates'] : $_GET['dates']);

if (count($dates) === 1) {
    $dates = [DateTime::createFromFormat('d/m/Y', $dates[0])];

} else if (count($dates) === 2) {
    $begin = DateTime::createFromFormat('d/m/Y', $dates[0]);
    $begin->setTime(0, 0, 0);

    $end = DateTime::createFromFormat('d/m/Y', $dates[1]);
    $end->setTime(0, 0, 1);

    $dates = new DatePeriod($begin, new DateInterval('P1D'), $end);

} else {
    $dates = [];
}

foreach ($dates as $date) {
    $harvestEntries = $harvest->getEntriesForDate($date);
    $session->addEntries($harvestEntries);
    $entriesCount += count($harvestEntries);
}

echo json_encode([
    'count' => $entriesCount
]);