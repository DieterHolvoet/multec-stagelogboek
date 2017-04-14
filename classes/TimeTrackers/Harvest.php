<?php

namespace TimeTrackers;

use DateTime;
use Harvest\HarvestAPI;
use Harvest\Model\DayEntry;
use StageLogboek\Entry;

class Harvest
{
    private $api;
    private $user;
    private $password;
    private $account;

    /**
     * Harvest constructor.
     * @param $user
     * @param $password
     * @param $account
     */
    public function __construct($user, $password, $account)
    {
        $this->user = $user;
        $this->password = $password;
        $this->account = $account;

        $this->api = new HarvestApi();
        $this->api->setUser($user);
        $this->api->setPassword($password);
        $this->api->setAccount($account);
    }

    /**
     * Get all Harvest entries for a specific date
     * @param DateTime $date
     * @return array
     */
    public function getEntriesForDate(DateTime $date)
    {
        $result = $this->api->getDailyActivity(
            intval($date->format('z')) + 1,
            $date->format('Y')
        );

        $harvestEntries = $result->get('data')->get('dayEntries');

        return array_map(function ($entry) {
            return $this->transformEntry($entry);
        }, $harvestEntries);
    }

    /**
     * Transform an Harvest API entry to a Stagelogboek entry
     * @param DayEntry $entry
     * @return Entry
     */
    private function transformEntry(DayEntry $entry)
    {
        return new Entry(
            new DateTime("{$entry->get('spent-at')} {$entry->get('started-at')}"),
            new DateTime("{$entry->get('spent-at')} {$entry->get('ended-at')}"),
            "{$entry->get('project')} ({$entry->get('task')})" . (!empty($entry->get('notes')) ? ": {$entry->get('notes')}" : ''),
            [4, 6, 10, 13]
        );
    }
}