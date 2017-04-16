<?php
    include '../vendor/autoload.php';

    // Initialise settings
    $env = M1\Env\Parser::parse(file_get_contents('../.env'));

    // Checks
    $hasHarvest = !empty($env['HARVEST_USER']) && !empty($env['HARVEST_PW']) && !empty($env['HARVEST_ACCOUNT']);
    $hasToggl = !empty($env['TOGGL_API_TOKEN']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Stagelogboek</title>

    <link rel="stylesheet" href="assets/flatpickr.css">
    <script src="assets/flatpickr.js"></script>

    <link rel="stylesheet" href="assets/global.css">
    <script src="assets/global.js"></script>
</head>
<body>
    <section class="section is-medium has-text-centered">
        <div class="container">
            <h1 class="title is-1">Stagelogboek</h1>
            <h4 class="subtitle is-4">Selecteer een datumreeks om te importeren</h4>
            <p id="datepicker"></p>
            <div class="field">
                <?php if ($hasHarvest): ?>
                    <div class="column">
                        <button data-name="harvest" class="btn-import button is-primary is-large is-loading ">Importeren van Harvest</button>
                    </div>
                <?php endif; ?>
                <?php if ($hasToggl): ?>
                    <div class="column">
                        <button data-name="toggl" class="btn-import button is-primary is-large is-loading ">Importeren van Toggl</button>
                    </div>
                <?php endif;?>
                <?php if (!$hasToggl && !$hasHarvest): ?>
                    <div class="column">
                        <button class="button" disabled>Geen verbonden accounts. Vul je .env-bestand in!</button>
                    </div>
                <?php endif;?>
            </div>
        </div>
    </section>
</body>
</html>