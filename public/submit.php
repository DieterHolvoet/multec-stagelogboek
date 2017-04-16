<?php

include '../vendor/autoload.php';

// Get settings
$env = M1\Env\Parser::parse(file_get_contents('../.env'));

// Get parameters
$dates = isset($_POST['dates']) ? $_POST['dates'] : $_GET['dates'];
$trackerName = isset($_POST['trackerName']) ? $_POST['trackerName'] : $_GET['trackerName'];

if (empty($dates)) {
    return json_encode(['error' => 'Geen datums meegegeven!']);
} elseif (empty($trackerName)) {
    return json_encode(['error' => 'Geen trackernaam meegegeven!']);
}

// Initialise Stagelogboek
$hasStagelogboek = !empty($env['STAGELOGBOEK_USER']) && !empty($env['STAGELOGBOEK_PW']);

if ($hasStagelogboek) {
    $session = new StageLogboek\Session($env['STAGELOGBOEK_USER'], $env['STAGELOGBOEK_PW']);
} else {
    return json_encode(['error' => 'Geen verbonden stagelogboek-account. Vul je .env-bestand in!']);
}

// Initialise time tracker
$hasHarvest = !empty($env['HARVEST_USER']) && !empty($env['HARVEST_PW']) && !empty($env['HARVEST_ACCOUNT']) && !empty($env['HARVEST_TEMPLATE']);
$hasToggl = !empty($env['TOGGL_API_TOKEN']) && !empty($env['TOGGL_TEMPLATE']);

if ($hasHarvest && $trackerName === 'harvest') {
    $tracker = new TimeTrackers\Harvest($env['HARVEST_USER'], $env['HARVEST_PW'], $env['HARVEST_ACCOUNT'], $env['HARVEST_TEMPLATE']);
} elseif ($hasToggl && $trackerName === 'toggl') {
    $tracker = new TimeTrackers\Toggl($env['TOGGL_API_TOKEN'], $env['TOGGL_TEMPLATE'], $env['TOGGL_WORKSPACE']);
} else {
    return json_encode(['error' => 'Geen verbonden time trackers. Vul je .env-bestand in!']);
}

// Initialise entries count
$entriesCount = 0;

// Get dates
$dates = explode(',', $dates);

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
    $entries = $tracker->getEntriesForDate($date);
    $session->addEntries($entries);
    $entriesCount += count($entries);
}

echo json_encode([
    'trackerName' => $trackerName,
    'addedCount' => $entriesCount
]);