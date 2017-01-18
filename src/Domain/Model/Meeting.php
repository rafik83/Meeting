<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Proximum\Vimeet\Domain\Model\Meeting\MessageSubjectInterface;
use Proximum\Vimeet\Domain\Model\Meeting\Request;

class Meeting implements MessageSubjectInterface
{
    const STATE_SCHEDULED = 'scheduled';
    const STATE_CANCELED  = 'canceled';

    /**
     * @var int
     */
    private $id;

    /**
     * @var MeetingSlot
     */
    private $slot;

    /**
     * @var Sheet
     */
    private $fromSheet;

    /**
     * @var ArrayCollection
     */
    private $fromParticipants;

    /**
     * @var Sheet
     */
    private $toSheet;

    /**
     * @var ArrayCollection
     */
    private $toParticipants;

    /**
     * @var \DateTimeInterface
     */
    private $createdAt;

    /**
     * @var string
     */
    private $state = self::STATE_SCHEDULED;

    /**
     * @var Spot
     */
    private $spot;

    /**
     * @var Request
     */
    private $request;

    /**
     * @var bool
     */
    private $blockedSpot = false;

    /**
     * @var bool
     */
    private $blockedSlot = false;

    /**
     * Meeting constructor.
     *
     * @param Request            $request
     * @param MeetingSlot        $slot
     * @param Sheet              $fromSheet
     * @param array              $fromParticipants
     * @param Sheet              $toSheet
     * @param array              $toParticipants
     * @param \DateTimeInterface $createdAt
     * @param Spot               $spot
     */
    public function __construct(
        Request $request,
        MeetingSlot $slot,
        Sheet $fromSheet,
        array $fromParticipants,
        Sheet $toSheet,
        array $toParticipants,
        \DateTimeInterface $createdAt,
        Spot $spot
    ) {
        $this->request          = $request;
        $this->slot             = $slot;
        $this->fromSheet        = $fromSheet;
        $this->fromParticipants = new ArrayCollection($fromParticipants);
        $this->toSheet          = $toSheet;
        $this->toParticipants   = new ArrayCollection($toParticipants);
        $this->createdAt        = $createdAt;
        $this->spot             = $spot;
    }

    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return Sheet
     */
    public function getFromSheet()
    {
        return $this->fromSheet;
    }

    /**
     * @return ArrayCollection
     */
    public function getFromParticipants()
    {
        return $this->fromParticipants;
    }

    /**
     * @param Participant $participant
     *
     * @return Meeting
     */
    public function addFromParticipant(Participant $participant)
    {
        $this->fromParticipants[$participant->getId()] = $participant;

        return $this;
    }

    /**
     * @param Participant $participant
     *
     * @return Meeting
     */
    public function removeFromParticipant(Participant $participant)
    {
        $this->fromParticipants->removeElement($participant);

        return $this;
    }

    /**
     * @param Participant $participant
     *
     * @return bool
     */
    public function hasFromParticipant(Participant $participant)
    {
        return $this->fromParticipants->contains($participant);
    }

    /**
     * @return Sheet
     */
    public function getToSheet()
    {
        return $this->toSheet;
    }

    /**
     * @return ArrayCollection
     */
    public function getToParticipants()
    {
        return $this->toParticipants;
    }

    /**
     * Get slot
     *
     * @return MeetingSlot
     */
    public function getSlot()
    {
        return $this->slot;
    }

    /**
     * @param Participant $participant
     *
     * @return Meeting
     */
    public function addToParticipant(Participant $participant)
    {
        $this->toParticipants[] = $participant;

        return $this;
    }

    /**
     * @param Participant $participant
     *
     * @return Meeting
     */
    public function removeToParticipant(Participant $participant)
    {
        $this->toParticipants->removeElement($participant);

        return $this;
    }

    /**
     * @param Participant $participant
     *
     * @return bool
     */
    public function hasToParticipant(Participant $participant)
    {
        return $this->toParticipants->contains($participant);
    }

    /**
     * @return \DateTimeInterface
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Get state
     *
     * @return string
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * @return Meeting
     */
    public function cancel()
    {
        $this->state = self::STATE_CANCELED;

        return $this;
    }

    /**
     * @return Spot
     */
    public function getSpot()
    {
        return $this->spot;
    }

    /**
     * @return Request
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * Guess what sheet is met by the given sheet
     *
     * @param Sheet $sheet
     *
     * @return Sheet|null
     */
    public function getSheetMet(Sheet $sheet)
    {
        if ($this->fromSheet === $sheet) {
            return $this->toSheet;
        }

        return $this->fromSheet;
    }

    /**
     * @return int
     */
    public function countParticipants()
    {
        return count($this->fromParticipants) + count($this->toParticipants);
    }

    /**
     * @param Spot $spot
     * @param bool $blockedSpot
     * @param bool $blockedSlot
     */
    public function updateSpot(Spot $spot, $blockedSpot, $blockedSlot)
    {
        $this->spot = $spot;
    }
}
