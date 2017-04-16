<?php

namespace TimeTrackers;

use DateTime;
use Harvest\HarvestAPI;
use Harvest\Model\DayEntry;
use StageLogboek\Competence;
use StageLogboek\Entry;
use StageLogboek\Parser;

class Harvest implements TimeTrackerInterface
{
    private $api;
    private $user;
    private $password;
    private $account;
    private $template;

    /**
     * Harvest constructor.
     * @param $user
     * @param $password
     * @param $account
     * @param $template
     */
    public function __construct($user, $password, $account, $template)
    {
        $this->user = $user;
        $this->password = $password;
        $this->account = $account;
        $this->template = $template;

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
     * Transform a Harvest entry to a Stagelogboek entry
     * @param DayEntry $entry
     * @return bool|Entry
     */
    public function transformEntry($entry)
    {
        if (!$entry instanceof DayEntry) {
            return false;
        }

        return new Entry(
            new DateTime("{$entry->get('spent-at')} {$entry->get('started-at')}"),
            new DateTime("{$entry->get('spent-at')} {$entry->get('ended-at')}"),
            Parser::parseTemplate($this->template, [
                'project' => $entry->get('project'),
                'task' => $entry->get('task'),
                'description' => $entry->get('notes'),
            ]),
            [
                Competence::APPLICATION_OF_KNOWLEDGE_AND_THEORETICAL_PERCEPTION,
                Competence::EFFORT_AND_PERSEVERANCE,
                Competence::CREATIVITY_AND_INNOVATION,
                Competence::QUALITY_ASSURANCE_AND_PRACTICALITY
            ]
        );
    }
}