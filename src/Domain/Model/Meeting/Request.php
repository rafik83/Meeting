<?php

namespace Proximum\Vimeet\Domain\Model\Meeting;

use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Proximum\Vimeet\Domain\Exception\Meeting\NoSheetForUserException;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Meeting;
use Proximum\Vimeet\Domain\Model\MeetingSlot;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;

class Request implements MessageSubjectInterface
{
    const STATE_SENT     = 'sent';
    const STATE_APPROVED = 'approved';
    const STATE_REFUSED  = 'refused';
    const STATE_PLANNED  = 'planned';

    const TYPE_REQUEST     = 'request';
    const TYPE_PROPOSITION = 'proposition';

    /**
     * @var int
     */
    private $id;

    /**
     * @var Event
     */
    private $event;

    /**
     * @var Sheet
     */
    private $from;

    /**
     * @var ArrayCollection
     */
    private $fromParticipants;

    /**
     * @var Sheet
     */
    private $to;

    /**
     * @var ArrayCollection
     */
    private $toParticipants;

    /**
     * @var string
     */
    private $state;

    /**
     * @var \DateTimeInterface
     */
    private $createdAt;

    /**
     * @var MeetingSlot
     */
    private $meetingSlot;

    /**
     * @var ArrayCollection
     */
    private $meeting;

    /**
     * @var User
     */
    private $creator;

    /**
     * @var \DateTimeInterface
     */
    private $stateUpdatedAt;

    /**
     * @var bool
     */
    private $disabled;

    /**
     * Aggregate to know if a request has messages
     *
     * @var bool
     */
    private $hasMessage;

    /** @var Message|null */
    private $updateOrDeleteReasonMessage;

    /** @var bool */
    private $fromPriority;

    /** @var bool */
    private $toPriority;

    /**
     * Request constructor.
     *
     * @param Sheet              $from
     * @param array              $fromParticipants
     * @param Sheet              $to
     * @param array              $toParticipants
     * @param \DateTimeInterface $createdAt
     * @param User               $creator
     * @param Event              $event
     * @param bool               $disabled
     * @param bool               $hasMessage
     * @param bool               $fromPriority
     * @param bool               $toPriority
     */
    public function __construct(
        Sheet $from,
        array $fromParticipants,
        Sheet $to,
        array $toParticipants,
        DateTimeInterface $createdAt,
        User $creator,
        Event $event,
        $disabled = false,
        $hasMessage = false,
        $fromPriority = false,
        $toPriority = false
    ) {
        $this->from = $from;
        $this->fromParticipants = new ArrayCollection($fromParticipants);
        $this->to = $to;
        $this->toParticipants = new ArrayCollection($toParticipants);
        $this->state = self::STATE_SENT;
        $this->createdAt = $createdAt;
        $this->stateUpdatedAt = $createdAt;
        $this->creator = $creator;
        $this->disabled = $disabled;
        $this->meeting = new ArrayCollection();
        $this->hasMessage = $hasMessage;
        $this->event = $event;
        $this->fromPriority = $fromPriority;
        $this->toPriority = $toPriority;
    }

    /**
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
        return $this->from;
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
    public function getFromParticipantsArray()
    {
        return $this->fromParticipants->toArray();
    }

    /**
     * @return Sheet
     */
    public function getToSheet()
    {
        return $this->to;
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
    public function getToParticipantsArray()
    {
        return $this->toParticipants->toArray();
    }

    /**
     * @return string
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * @return \DateTimeInterface
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @return \DateTimeInterface
     */
    public function getStateUpdatedAt()
    {
        return $this->stateUpdatedAt;
    }

    /**
     * @param \DateTimeInterface $date
     *
     * @return Request
     */
    public function refuse(\DateTimeInterface $date)
    {
        $this->state          = self::STATE_REFUSED;
        $this->stateUpdatedAt = $date;

        return $this;
    }

    /**
     * @param \DateTimeInterface $date
     *
     * @return Request
     */
    public function approve(\DateTimeInterface $date)
    {
        $this->state          = self::STATE_APPROVED;
        $this->stateUpdatedAt = $date;

        return $this;
    }

    /**
     * @param DateTimeInterface $date
     */
    public function unRefuse(\DateTimeInterface $date)
    {
        $this->state          = self::STATE_SENT;
        $this->stateUpdatedAt = $date;
    }

    /**
     * @param DateTimeInterface $date
     */
    public function unApprove(\DateTimeInterface $date)
    {
        $this->state          = self::STATE_SENT;
        $this->stateUpdatedAt = $date;
    }

    /**
     * @deprecated
     *
     * @param string $state
     */
    public function setState($state)
    {
        $this->state = $state;
    }

    /**
     * @param Participant $participant
     *
     * @return Request
     */
    public function addToParticipant(Participant $participant)
    {
        $this->toParticipants[] = $participant;

        return $this;
    }

    /**
     * Get meetingSlot
     *
     * @return MeetingSlot
     */
    public function getMeetingSlot()
    {
        return $this->meetingSlot;
    }

    /**
     * Set meetingSlot
     *
     * @param MeetingSlot $meetingSlot
     *
     * @return Request
     */
    public function setMeetingSlot($meetingSlot)
    {
        $this->meetingSlot = $meetingSlot;

        return $this;
    }

    /**
     * Get meeting
     *
     * @return null|Meeting
     */
    public function getMeeting()
    {
        $meeting = $this->meeting->first();

        if (false === $meeting) {
            return null;
        }

        return $meeting;
    }

    /**
     * Set meeting
     *
     * @param Meeting $meeting
     *
     * @return Request
     */
    public function setMeeting(Meeting $meeting)
    {
        $this->meeting->add($meeting);

        return $this;
    }

    /**
     * @return User
     */
    public function getCreator()
    {
        return $this->creator;
    }

    /**
     * @return bool
     */
    public function hasToParticipants()
    {
        return !$this->toParticipants->isEmpty();
    }

    /**
     * @return bool
     */
    public function hasFromParticipants()
    {
        return !$this->fromParticipants->isEmpty();
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
     * @param Participant $participant
     *
     * @return bool
     */
    public function hasToParticipant(Participant $participant)
    {
        return $this->toParticipants->contains($participant);
    }

    /**
     * @param Participant $fromParticipant
     *
     * @return Request
     */
    public function addFromParticipant(Participant $fromParticipant)
    {
        $this->fromParticipants->add($fromParticipant);

        return $this;
    }

    /**
     * @param Participant $participant
     *
     * @return Request
     */
    public function removeToParticipant(Participant $participant)
    {
        $this->toParticipants->removeElement($participant);

        return $this;
    }

    /**
     * @param Participant $participant
     *
     * @return Request
     */
    public function removeFromParticipant(Participant $participant)
    {
        $this->fromParticipants->removeElement($participant);

        return $this;
    }

    /**
     * @param Participant[] $participants
     */
    public function updateFromParticipants(array $participants)
    {
        $this->fromParticipants->clear();

        foreach ($participants as $participant) {
            $this->fromParticipants->add($participant);
        }
    }

    /**
     * @param Participant[] $participants
     */
    public function updateToParticipants(array $participants)
    {
        $this->toParticipants->clear();

        foreach ($participants as $participant) {
            $this->toParticipants->add($participant);
        }
    }

    /**
     * @return bool
     */
    public function isSent()
    {
        return self::STATE_SENT === $this->state;
    }

    /**
     * @return bool
     */
    public function isApproved()
    {
        return self::STATE_APPROVED === $this->state;
    }

    /**
     * @return bool
     */
    public function isRefused()
    {
        return self::STATE_REFUSED === $this->state;
    }

    /**
     * @return array
     */
    public static function getAllStates()
    {
        return [
            self::STATE_SENT,
            self::STATE_APPROVED,
            self::STATE_REFUSED,
        ];
    }

    /**
     * @param Sheet $sheet
     *
     * @return bool
     */
    public function isSender(Sheet $sheet)
    {
        return $this->from === $sheet;
    }

    /**
     * @param Sheet $sheet
     *
     * @return bool
     */
    public function isReceiver(Sheet $sheet)
    {
        return $this->to === $sheet;
    }

    /**
     * @param Sheet $sheet
     *
     * @return bool
     */
    public function hasNoPreference(Sheet $sheet)
    {
        if ($this->from === $sheet) {
            return $this->fromParticipants->isEmpty();
        }

        if ($this->to === $sheet) {
            return $this->toParticipants->isEmpty();
        }

        if ($this->from->hasLinkedSheet($sheet)) {
            return $this->fromParticipants->isEmpty();
        }

        if ($this->to->hasLinkedSheet($sheet)) {
            return $this->toParticipants->isEmpty();
        }

        throw new \InvalidArgumentException('Sheet not concerned by this meeting request');
    }

    /**
     * @return bool
     */
    public function isTransformableIntoMeeting()
    {
        return TransformableRequest::isTransformable($this);
    }

    /**
     * @param Sheet $sheet
     *
     * @return Sheet
     */
    public function getSheetMet(Sheet $sheet)
    {
        if ($this->isSender($sheet)) {
            return $this->to;
        }

        if ($this->isReceiver($sheet)) {
            return $this->from;
        }

        throw new \InvalidArgumentException('Sheet not concerned by this meeting request');
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
        if ($this->from->hasUser($user)) {
            return $this->from;
        }

        if ($this->to->hasUser($user)) {
            return $this->to;
        }

        throw new NoSheetForUserException();
    }

    /**
     *  In many cases, prefer \Proximum\Vimeet\Domain\Meeting\MeetingParticipants::getMeetingParticipants
     *
     * "Quand la demande de RDV n'a pas de participant de préférence et que la liste
     *  des participants disponible est vide on utilise le seul participant de la fiche"
     *
     * @param Sheet $sheet
     *
     * @return Participant[]
     */
    public function getParticipants(Sheet $sheet)
    {
        if ($this->hasNoPreference($sheet) && $sheet->hasOnlyOneParticipant()) {
            return [$sheet->getFirstParticipant()];
        }

        if ($this->isSender($sheet)) {
            return $this->fromParticipants->toArray();
        }

        if ($this->isReceiver($sheet)) {
            return $this->toParticipants->toArray();
        }

        throw new \InvalidArgumentException('Sheet not concerned by this meeting request');
    }

    /**
     * @param Sheet $sheet
     *
     * @return Participant[]
     */
    public function getParticipantsOfSheetInRequest(Sheet $sheet)
    {
        if ($this->isSender($sheet)) {
            return $this->fromParticipants->toArray();
        }

        if ($this->isReceiver($sheet)) {
            return $this->toParticipants->toArray();
        }

        return [];
    }

    /**
     * @return bool
     */
    public function hasMeeting()
    {
        return null !== $this->getMeeting();
    }

    /**
     * @return Event
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * @return bool
     */
    public function isDisabled()
    {
        return $this->disabled;
    }

    /**
     * @param bool $disabled
     */
    public function setDisabled($disabled)
    {
        $this->disabled = $disabled;
    }

    /**
     * @param Sheet $sheet
     *
     * @return null|string
     */
    public function getTypeOfRequest(Sheet $sheet)
    {
        if ($this->isSender($sheet)) {
            return self::TYPE_REQUEST;
        }

        if ($this->isReceiver($sheet)) {
            return self::TYPE_PROPOSITION;
        }

        return null;
    }

    /**
     * @param bool $hasMessage
     */
    public function setHasMessage($hasMessage)
    {
        $this->hasMessage = $hasMessage;
    }

    /**
     * @return bool
     */
    public function hasMessage()
    {
        return $this->hasMessage;
    }

    /**
     * @return bool
     */
    public function isOneOfSheetsNotAttend()
    {
        return !$this->getFromSheet()->attend() || !$this->getFromSheet()->isEnabled()
            || !$this->getToSheet()->attend() || !$this->getToSheet()->isEnabled()
        ;
    }

    public function setUpdateOrDeleteReasonMessage(?Message $updateOrDeleteReasonMessage): void
    {
        $this->updateOrDeleteReasonMessage = $updateOrDeleteReasonMessage;
    }

    public function getUpdateOrDeleteReasonMessage(): ?Message
    {
        return $this->updateOrDeleteReasonMessage;
    }

    public function isFromPriority(): bool
    {
        return $this->fromPriority;
    }

    public function setFromPriority(bool $fromPriority): void
    {
        $this->fromPriority = $fromPriority;
    }

    public function isToPriority(): bool
    {
        return $this->toPriority;
    }

    public function setToPriority(bool $toPriority): void
    {
        $this->toPriority = $toPriority;
    }
}
