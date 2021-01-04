<?php

namespace Proximum\Vimeet\Application\View\Meeting\RequestTransformIntoMeeting;

use Proximum\Vimeet\Domain\Model\MeetingSlot;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Spot;

class AvailableMeetingView
{
    /**
     * @var MeetingSlot
     */
    public $slot;

    /**
     * @var Sheet
     */
    public $fromSheet;

    /**
     * @var Sheet
     */
    public $toSheet;

    /**
     * @var Participant[]
     */
    public $fromParticipants = [];

    /**
     * @var Participant[]
     */
    public $toParticipants = [];

    /**
     * @var bool
     */
    public $fromSheetHasNoPreference;

    /**
     * @var bool
     */
    public $toSheetHasNoPreference;

    /**
     * @var null|Spot
     */
    public $spot;

    /**
     * @var bool
     */
    public $fromParticipantIsPhoneValidated = false;

    /**
     * @var bool
     */
    public $toParticipantIsPhoneValidated = false;

    /**
     * @param MeetingSlot   $slot
     * @param Sheet         $fromSheet
     * @param Sheet         $toSheet
     * @param Participant[] $fromParticipants
     * @param Participant[] $toParticipants
     * @param bool          $fromSheetHasNoPreference
     * @param bool          $toSheetHasNoPreference
     */
    public function __construct(
        MeetingSlot $slot,
        Sheet $fromSheet,
        Sheet $toSheet,
        array $fromParticipants,
        array $toParticipants,
        bool $fromSheetHasNoPreference,
        bool $toSheetHasNoPreference
    ) {
        $this->slot                     = $slot;
        $this->fromSheet                = $fromSheet;
        $this->toSheet                  = $toSheet;
        $this->fromParticipants         = $fromParticipants;
        $this->toParticipants           = $toParticipants;
        $this->fromSheetHasNoPreference = $fromSheetHasNoPreference;
        $this->toSheetHasNoPreference   = $toSheetHasNoPreference;
    }

    /**
     * @return int
     */
    public function getTotalParticipants(): int
    {
        return count($this->fromParticipants) + count($this->toParticipants);
    }

    /**
     * @throws \LogicException
     *
     * @return Participant
     */
    public function getFromParticipant()
    {
        return $this->getParticipant($this->fromParticipants);
    }

    /**
     * @throws \LogicException
     *
     * @return Participant
     */
    public function getToParticipant()
    {
        return $this->getParticipant($this->toParticipants);
    }

    /**
     * @param array $participants
     *
     * @throws \LogicException
     *
     * @return Participant
     */
    private function getParticipant(array $participants)
    {
        $participant = reset($participants);

        if (false === $participant || 1 !== count($participants)) {
            throw new \LogicException('This method can be used only if only one participant');
        }

        return $participant;
    }
}
