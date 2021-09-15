<?php

namespace Proximum\Vimeet\Domain\Model\Meeting;

use Proximum\Vimeet\Domain\Model\Meeting;
use Proximum\Vimeet\Domain\Model\Participant;

class ParticipantExtraData
{
    public const TYPE_PRESENCE = 'presence';

    /** @var int */
    private $id;

    /** @var string */
    private $type;

    /** @var Participant */
    private $participant;

    /** @var Meeting */
    private $meeting;

    /** @var null|\DateTimeInterface */
    private $date;

    public function __construct(
        string $type,
        Participant $participant,
        Meeting $meeting,
        ?\DateTimeInterface $date = null
    ) {
        $this->type = $type;
        $this->participant = $participant;
        $this->meeting = $meeting;
        $this->date = $date;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getParticipant(): Participant
    {
        return $this->participant;
    }

    public function getMeeting(): Meeting
    {
        return $this->meeting;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $dateTime): void
    {
        $this->date = $dateTime;
    }
}
