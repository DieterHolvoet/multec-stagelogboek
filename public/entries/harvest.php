<?php

use M1\Env\Parser;
use TimeTrackers\Harvest;

include '../../vendor/autoload.php';

$env = Parser::parse(file_get_contents('../../.env'));
$harvest = new Harvest($env['HARVEST_USER'], $env['HARVEST_PW'], $env['HARVEST_ACCOUNT']);

if (isset($_GET['date'])) {
    $entries = $harvest->getEntriesForDate(DateTime::createFromFormat('U', $_GET['date']));
} else {
    $entries = [
        'error' => 'Invalid date'
    ];
}

header('Content-type: application/json');
echo json_encode($entries);