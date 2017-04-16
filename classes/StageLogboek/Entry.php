<?php

namespace StageLogboek;

use DateTime;
use JsonSerializable;

class Entry implements JsonSerializable
{
    /** @var DateTime */
    private $start;
    /** @var DateTime */
    private $end;
    /** @var string */
    private $description;
    /** @var int[] */
    private $competences;

    /**
     * Entry constructor.
     * @param DateTime $start
     * @param DateTime $end
     * @param string $description
     * @param \int[] $competences
     */
    public function __construct(DateTime $start, DateTime $end, $description, array $competences)
    {
        $this->start = $start;
        $this->end = $end;
        $this->description = $description;
        $this->competences = $competences;
    }

    /**
     * @return DateTime
     */
    public function getStart()
    {
        return $this->start;
    }

    /**
     * @return DateTime
     */
    public function getEnd()
    {
        return $this->end;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @return int[]
     */
    public function getCompetences()
    {
        return $this->competences;
    }

    /**
     * Specify data which should be serialized to JSON
     * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4.0
     */
    function jsonSerialize()
    {
        return [
            'start' => $this->start->format('d/m/Y h:i:s'),
            'end' => $this->end->format('d/m/Y h:i:s'),
            'description' => $this->description,
            'questions' => $this->questions,
        ];
    }
}