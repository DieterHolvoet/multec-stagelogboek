<?php
/**
 * Created by PhpStorm.
 * User: Dieter
 * Date: 14/04/2017
 * Time: 20:41
 */

namespace TimeTrackers;

use DateTime;
use MorningTrain\TogglApi\TogglApi;
use StageLogboek\Competence;
use StageLogboek\Entry;
use StageLogboek\Parser;

class Toggl implements TimeTrackerInterface
{
    private $api;
    private $apiKey;
    private $workspace;
    private $template;

    /**
     * Toggl constructor.
     * @param string $apiKey
     * @param string $template
     * @param string $workspace
     */
    public function __construct($apiKey, $template, $workspace)
    {
        $this->apiKey = $apiKey;
        $this->api = new TogglApi($apiKey);
        $this->template = $template;
        $this->workspace = $workspace;
    }

    /**
     * Get all Toggl entries for a specific date
     * @param DateTime $date
     * @return array
     */
    function getEntriesForDate(DateTime $date)
    {
        $togglEntries = $this->api->getTimeEntriesInRange($date, $date);
        $workspaces = $this->api->getWorkspaces();

        // Filter workspaces
        if (!empty($this->workspace)) {
            $workspaces = array_filter($workspaces, function ($workspace) {
                return $workspace->name === $this->workspace;
            });

            if (!empty($workspaces)) {
                $wid = $workspaces[0]->id;

                $togglEntries = array_filter($togglEntries, function ($entry) use ($wid) {
                    return $entry->wid === $wid;
                });
            }
        }

        // Load extra info
        $togglEntries = array_map(function ($entry) {
            $entry = json_decode(json_encode($entry), true);
            $extra = [];

            if (!empty($entry['pid'])) {
                $project = $this->api->getProject($entry['pid']);
                $extra['project'] = $project->name;

                if ($this->workspace) {
                    $extra['workspace'] = $this->workspace;
                } elseif (!empty($project->wid)) {
                    $workspace = $this->api->getWorkspace($project->wid);
                    $extra['workspace'] = $workspace->name;
                }

                if (!empty($project->cid)) {
                    $client = $this->api->getClientById($project->cid);
                    $extra['client'] = $client->name;
                }
            }

            return array_merge($entry, $extra);
        }, $togglEntries);

        // Transform
        return array_map(function ($entry) {
            return $this->transformEntry($entry);
        }, $togglEntries);
    }

    /**
     * Transform a Toggl entry to a Stagelogboek entry
     * @param $entry
     * @return Entry
     */
    function transformEntry($entry)
    {
        return new Entry(
            new DateTime($entry['start']),
            new DateTime($entry['stop']),
            Parser::parseTemplate($this->template, [
                'description' => $entry['description'],
                'project' => $entry['project'],
                'workspace' => $entry['workspace'],
                'client' => $entry['client']
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