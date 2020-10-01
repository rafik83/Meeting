<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Proximum\Vimeet\Domain\Exception\Meeting\NoSheetForUserException;
use Proximum\Vimeet\Domain\Model\Meeting\MessageSubjectInterface;
use Proximum\Vimeet\Domain\Model\Meeting\Request;

class Meeting implements MessageSubjectInterface
{
    public const STATE_SCHEDULED = 'scheduled';
    public const STATE_CANCELED  = 'canceled';

    public const STATUS_NOT_CONFIRMED = 'not_confirmed';
    public const STATUS_CONFIRMED = 'confirmed';
    public const STATUS_CANCELED = 'canceled';

    public const CREATED_BY_PLANNER = 'planner';
    public const CREATED_BY_ADMIN = 'admin';
    public const CREATED_BY_PARTICIPANT = 'participant';

    public const CREATED_BY = [
        self::CREATED_BY_PLANNER,
        self::CREATED_BY_ADMIN,
        self::CREATED_BY_PARTICIPANT,
    ];

    public const STATUS_LIST = [
        self::STATUS_CANCELED,
        self::STATUS_NOT_CONFIRMED,
        self::STATUS_CONFIRMED,
    ];

    /**
     * @var int
     */
    private $id;

    /**
     * @var MeetingSlot
     */
    private $slot;

    /**
     * @var Event
     */
    private $event;

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

    /** @var string */
    private $createdType;

    /**
     * @var string
     *
     * @see self::STATUS_LIST
     */
    private $status;

    public function __construct(
        Request $request,
        MeetingSlot $slot,
        Sheet $fromSheet,
        array $fromParticipants,
        Sheet $toSheet,
        array $toParticipants,
        \DateTimeInterface $createdAt,
        Spot $spot,
        Event $event,
        bool $blockedSpot = false,
        bool $blockedSlot = false,
        string $createdType =  self::CREATED_BY_ADMIN
    ) {
        if (!in_array($createdType, self::CREATED_BY, true)) {
            throw new \InvalidArgumentException('$createdType is not valid');
        }

        $this->request = $request;
        $this->slot = $slot;
        $this->fromSheet = $fromSheet;
        $this->fromParticipants = new ArrayCollection($fromParticipants);
        $this->toSheet = $toSheet;
        $this->toParticipants = new ArrayCollection($toParticipants);
        $this->createdAt = $createdAt;
        $this->spot = $spot;
        $this->blockedSpot = $blockedSpot;
        $this->blockedSlot = $blockedSlot;
        $this->event = $event;
        $this->status = self::STATUS_NOT_CONFIRMED;
        $this->createdType = $createdType;
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
     * @return Participant[]
     */
    public function getFromParticipantsArray(): array
    {
        return $this->fromParticipants->toArray();
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
     * @return Participant[]
     */
    public function getToParticipantsArray(): array
    {
        return $this->toParticipants->toArray();
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
     * @return Sheet
     */
    public function getSheetMet(Sheet $sheet)
    {
        if ($this->fromSheet === $sheet) {
            return $this->toSheet;
        }

        if ($this->toSheet === $sheet) {
            return $this->fromSheet;
        }

        if ($this->fromSheet->hasLinkedSheet($sheet)) {
            return $this->toSheet;
        }

        if ($this->toSheet->hasLinkedSheet($sheet)) {
            return $this->fromSheet;
        }

        throw new \InvalidArgumentException('Sheet unknown for this meeting');
    }

    /**
     * @param User $user
     *
     * @throws NoSheetForUserException
     *
     * @return Sheet
     */
    public function getSheetOfUser(User $user)
    {
        if ($this->fromSheet->hasUser($user)) {
            return $this->fromSheet;
        }

        if ($this->toSheet->hasUser($user)) {
            return $this->toSheet;
        }

        if ($this->fromSheet->hasUserInLinkedSheets($user)) {
            return $this->fromSheet;
        }

        if ($this->toSheet->hasUserInLinkedSheets($user)) {
            return $this->toSheet;
        }

        throw new NoSheetForUserException();
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
        $this->spot        = $spot;
        $this->blockedSpot = $blockedSpot;
        $this->blockedSlot = $blockedSlot;
    }

    /**
     * @param MeetingSlot $slot
     * @param Spot        $spot
     */
    public function updateSlotAndSpot(MeetingSlot $slot, Spot $spot)
    {
        $this->slot = $slot;
        $this->spot = $spot;
    }

    public function isBlockedSpot(): bool
    {
        return $this->blockedSpot;
    }

    /**
     * Set the blockedSpot to true
     */
    public function blockSpot()
    {
        $this->blockedSpot = true;
    }

    /**
     * Set the blockedSlot to true
     */
    public function blockSlot()
    {
        $this->blockedSlot = true;
    }

    public function isBlockedSlot(): bool
    {
        return $this->blockedSlot;
    }

    /**
     * @param Sheet $sheet
     *
     * @return Participant[]
     */
    public function getParticipants(Sheet $sheet): array
    {
        if ($sheet === $this->fromSheet) {
            return $this->getFromParticipantsArray();
        } elseif ($sheet === $this->toSheet) {
            return $this->getToParticipantsArray();
        }

        return [];
    }

    /**
     * @param Sheet $sheet
     *
     * @return Participant[]
     */
    public function getMetParticipants(Sheet $sheet): array
    {
        if ($sheet === $this->fromSheet) {
            return $this->getToParticipantsArray();
        }

        if ($sheet === $this->toSheet) {
            return $this->getFromParticipantsArray();
        }

        return [];
    }

    public function addParticipant(Participant $participant): void
    {
        if ($participant->getSheet()->getId() === $this->fromSheet->getId()) {
            $this->fromParticipants->add($participant);

            return;
        }

        if ($participant->getSheet()->getId() === $this->toSheet->getId()) {
            $this->toParticipants->add($participant);

            return;
        }

        throw new \InvalidArgumentException('Participant sheet not known for this meeting');
    }

    public function hasParticipant(Participant $participant): bool
    {
       return in_array($participant, $this->getAllParticipants(), true);
    }

    public function getCreatedType(): string
    {
        return $this->createdType;
    }

    public function setCreatedType(string $createdType): void
    {
        $this->createdType = $createdType;
    }

    public function isCreatedByParticipants(): bool
    {
        return $this->createdType === self::CREATED_BY_PARTICIPANT;
    }

    /**
     * @return Sheet[]
     */
    public function getSheets()
    {
        return [$this->fromSheet, $this->toSheet];
    }

    public function hasSheet(Sheet $sheet): bool
    {
        return \in_array($sheet, $this->getSheets(), true);
    }

    /**
     * @return Participant[]
     */
    public function getAllParticipants()
    {
        return array_merge($this->getFromParticipantsArray(), $this->getToParticipantsArray());
    }

    /**
     * @return Event
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * @param User $user
     *
     * @return Sheet|null
     */
    public function getSheetByUser(User $user)
    {
        foreach ($this->getToParticipants() as $participant) {
            if ($participant->getUser()->getId() === $user->getId()) {
                return $this->toSheet;
            }
        }

        foreach ($this->getFromParticipants() as $participant) {
            if ($participant->getUser()->getId() === $user->getId()) {
                return $this->fromSheet;
            }
        }

        return null;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): void
    {
        $this->status = $status;
    }

    public function resetStatus(): void
    {
        $this->status = self::STATUS_NOT_CONFIRMED;
    }

    public function isVisio(): bool
    {
        return $this->getSpot()->isVisio();
    }
}
