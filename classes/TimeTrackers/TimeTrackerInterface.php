<?php
/**
 * Created by PhpStorm.
 * User: Dieter
 * Date: 14/04/2017
 * Time: 20:18
 */

namespace TimeTrackers;

use DateTime;
use StageLogboek\Entry;

interface TimeTrackerInterface
{
    /**
     * Get all time tracker entries for a specific date
     * @param DateTime $date
     * @return array
     */
    function getEntriesForDate(DateTime $date);

    /**
     * Transform a time tracker entry to a Stagelogboek entry
     * @param $timeTrackerEntry
     * @return Entry
     */
    function transformEntry($timeTrackerEntry);
}