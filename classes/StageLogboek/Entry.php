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
    /** @var array */
    private $questions;

    /**
     * StageLogboekEntry constructor.
     * @param DateTime $start
     * @param DateTime $end
     * @param string $description
     * @param array $questions
     */
    public function __construct(DateTime $start, DateTime $end, $description, array $questions)
    {
        $this->start = $start;
        $this->end = $end;
        $this->description = $description;
        $this->questions = $questions;
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
     * @return array
     */
    public function getQuestions()
    {
        return $this->questions;
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