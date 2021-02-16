<?php

namespace Proximum\Vimeet\Behat\Context\Domain;

use Behat\Behat\Context\Context;
use Proximum\Vimeet\Behat\Context\Domain\Proxy\MeetingContextProxyInterface;
use Proximum\Vimeet\Domain\Model\Participant;

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
     * @Given /^there is a meeting slot from (?P<fromDate>\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}) to (?P<toDate>\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})$/
     */
    public function thereIsAMeetingSlot(string $fromDate, string $toDate)
    {
        $event = $this->meetingContextProxy->getStorage()->get('event');

        if (null === $event) {
            throw new \InvalidArgumentException('Missing Event');
        }

        $meetingManager = $this->meetingContextProxy->getMeetingManager();

        $meetingSlot = $meetingManager->createMeetingSlot($event, new \DateTime($fromDate), new \DateTime($toDate));
        $this->meetingContextProxy->getStorage()->set('meetingSlot', $meetingSlot);
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
     * @param int    $slotId
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

    /**
     * @Given /^there is a meeting between "(?P<sheetTitle>[^"]+)" and "(?P<otherSheetTitle>[^"]+)" on spot "(?P<spotReference>[^"]+)"$/
     *
     * @param string $sheetTitle
     * @param string $otherSheetTitle
     * @param string $spotReference
     */
    public function thereIsAMeetingBetweenSheetAndSheetOnSpot($sheetTitle, $otherSheetTitle, $spotReference)
    {
        /** @var Participant|null $participant */
        $event = $this->meetingContextProxy->getStorage()->get('event');

        if (null === $event) {
            throw new \InvalidArgumentException('Missing event');
        }

        $meeting = $this->meetingContextProxy
            ->getMeetingManager()
            ->createMeetingForSheetsAndSpot($event, $sheetTitle, $otherSheetTitle, $spotReference);

        $this->meetingContextProxy->getStorage()->set('meeting', $meeting);
    }

    /**
     * @Given /^there is a meeting for this request on slot "(?P<slotId>\d+)" and spot "(?P<spotReference>[^"]+)"$/
     */
    public function thereIsAMeetingForThisRequest(string $slotId, string $spotReference)
    {
        $request = $this->meetingContextProxy->getStorage()->get('request');

        if (null === $request) {
            throw new \InvalidArgumentException('Missing Request');
        }

        $event = $this->meetingContextProxy->getStorage()->get('event');

        if (null === $event) {
            throw new \InvalidArgumentException('Missing Event');
        }

        $slot = $this->meetingContextProxy->getSlotManager()->findByEventAndId($event, $slotId);

        if (null === $slot) {
            throw new \InvalidArgumentException('Missing Slot');
        }

        $spot = $this->meetingContextProxy->getSpotManager()->getByReference($event, $spotReference);

        if (null === $spot) {
            throw new \InvalidArgumentException('Missing Spot');
        }

        $this->meetingContextProxy->getMeetingManager()
            ->createMeetingFromRequest($event, $request, $slot, $spot);
    }

    /**
     * @Given /^there is a video meeting for this participant$/
     */
    public function thereIsAVideoMeetingForThisParticipant()
    {
        /** @var Participant|null $participant */
        $participant = $this->meetingContextProxy->getStorage()->get('participant');
        $event = $this->meetingContextProxy->getStorage()->get('event');

        if (null === $participant) {
            throw new \InvalidArgumentException('Missing Participant');
        }

        $meetingManager = $this->meetingContextProxy->getMeetingManager();
        $meeting = $meetingManager->createVideoMeetingForParticipant($event, $participant);
        $this->meetingContextProxy->getStorage()->set('meeting', $meeting);
    }
}
