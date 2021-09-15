<?php

namespace Proximum\Vimeet\Domain\Model\Analytic;

use Proximum\Vimeet\Domain\Model\Event;

class MeetingSolution
{
    /** @var int */
    private $id;

    /** @var Event */
    private $event;

    /** @var \DateTimeInterface */
    private $createdAt;

    /** @var int */
    private $meetings;

    /** @var int */
    private $requests;

    /** @var int */
    private $fillingRate;

    /** @var string */
    private $sheetSatisfaction;

    /** @var string */
    private $spotSatisfaction;

    /** @var string */
    private $spotFillingGraph;

    /**
     * @param Event              $event
     * @param int                $meetings
     * @param int                $requests
     * @param int                $fillingRate
     * @param string             $sheetSatisfaction
     * @param string             $spotSatisfaction
     * @param string             $spotFillingGraph
     * @param \DateTimeInterface $createdAt
     */
    public function __construct(
        Event $event,
        int $meetings,
        int $requests,
        int $fillingRate,
        string $sheetSatisfaction,
        string $spotSatisfaction,
        string $spotFillingGraph,
        \DateTimeInterface $createdAt
    ) {
        $this->event = $event;
        $this->meetings = $meetings;
        $this->requests = $requests;
        $this->fillingRate = $fillingRate;
        $this->sheetSatisfaction = $sheetSatisfaction;
        $this->spotSatisfaction = $spotSatisfaction;
        $this->spotFillingGraph = $spotFillingGraph;
        $this->createdAt = $createdAt;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return Event
     */
    public function getEvent(): Event
    {
        return $this->event;
    }

    /**
     * @return \DateTimeInterface
     */
    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }

    /**
     * @return int
     */
    public function getMeetings(): int
    {
        return $this->meetings;
    }

    /**
     * @return int
     */
    public function getRequests(): int
    {
        return $this->requests;
    }

    /**
     * @return int
     */
    public function getFillingRate(): int
    {
        return $this->fillingRate;
    }

    /**
     * @return string json normalize
     */
    public function getSheetSatisfaction(): string
    {
        return $this->sheetSatisfaction;
    }

    /**
     * @return string json normalize
     */
    public function getSpotSatisfaction(): string
    {
        return $this->spotSatisfaction;
    }

    /**
     * @return string json normalize
     */
    public function getSpotFillingGraph(): string
    {
        return $this->spotFillingGraph;
    }
}
