<?php

namespace Proximum\Vimeet\Behat\Context\Domain;

use Behat\Behat\Context\Context;
use Proximum\Vimeet\Behat\Context\Domain\Proxy\MeetingContextProxyInterface;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;

class MeetingContext implements Context
{
    /** @var MeetingContextProxyInterface */
    private $meetingContextProxy;

    /**
     * @param MeetingContextProxyInterface $meetingContextProxy
     */
    public function __construct(MeetingContextProxyInterface $meetingContextProxy)
    {
        $this->meetingContextProxy = $meetingContextProxy;
    }

    /**
     * @Given /^there is a meeting on spot "(?P<spotReference>[^"]+)"$/
     *
     * @param string $spotReference
     */
    public function thereIsAMeetingOnSpot($spotReference)
    {
        $event = $this->meetingContextProxy->getStorage()->get('event');

        if (null === $event) {
            throw new \InvalidArgumentException('Missing Event');
        }

        $meetingManager = $this->meetingContextProxy->getMeetingManager();

        $meeting = $meetingManager->createMeetingOnSpot($event, $spotReference);
        $this->meetingContextProxy->getStorage()->set('meeting', $meeting);
    }

    /**
     * @Given /^there is a meeting on slot "(?P<slotId>\d+)"$/
     *
     * @param int $slotId
     */
    public function thereIsAMeetingOnSlot($slotId)
    {
        $event = $this->meetingContextProxy->getStorage()->get('event');

        if (null === $event) {
            throw new \InvalidArgumentException('Missing Event');
        }

        $meetingManager = $this->meetingContextProxy->getMeetingManager();

        $meeting = $meetingManager->createMeetingOnSlot($event, $slotId);
        $this->meetingContextProxy->getStorage()->set('meeting', $meeting);
    }

    /**
     * @Given /^there is a meeting on slot "(?P<slotId>\d+)" and spot "(?P<spotReference>[^"]+)" for this participant$/
     *
     * @param int $slotId
     * @param string $spotReference
     */
    public function thereIsAMeetingOnSlotAndSpotForThisParticipant($slotId, $spotReference)
    {
        /** @var Participant|null $participant */
        $participant = $this->meetingContextProxy->getStorage()->get('participant');

        if (null === $participant) {
            throw new \InvalidArgumentException('Missing Participant');
        }

        $meetingManager = $this->meetingContextProxy->getMeetingManager();
        $meeting        = $meetingManager->createMeetingForParticipantOnGivenSlotAndSpot(
            $participant,
            $slotId,
            $spotReference
        );
        $this->meetingContextProxy->getStorage()->set('meeting', $meeting);
    }
}
