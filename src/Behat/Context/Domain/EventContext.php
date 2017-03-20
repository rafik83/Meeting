<?php

namespace Proximum\Vimeet\Behat\Context\Domain;

use Behat\Behat\Context\Context;
use Proximum\Vimeet\Behat\Context\Domain\Proxy\EventContextProxyInterface;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Meeting;
use Proximum\Vimeet\Domain\Model\Meeting\Request;
use Proximum\Vimeet\Domain\Model\MeetingSlot;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Spot;
use Proximum\Vimeet\Domain\Model\Template\RegistrationTemplate;
use Proximum\Vimeet\Domain\Model\Template\SheetTemplate;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Tests\Factory\EventFactory;
use Proximum\Vimeet\Tests\Factory\ParticipantFactory;
use Proximum\Vimeet\Tests\Factory\SheetFactory;
use Proximum\Vimeet\Tests\Factory\UserFactory;

class EventContext implements Context
{
    /** @var EventContextProxyInterface */
    private $eventContextProxy;

    /**
     * @param EventContextProxyInterface $eventContextProxy
     */
    public function __construct(EventContextProxyInterface $eventContextProxy)
    {
        $this->eventContextProxy = $eventContextProxy;
    }

    /**
     * @Given /^the event "(?P<eventTitle>[^"]+)" is created$/
     *
     * @param string $eventTitle
     */
    public function createEvent($eventTitle = null)
    {
        $event = EventFactory::createEvent($eventTitle);
        $this->eventContextProxy->getEventRepository()->add($event);
        $this->eventContextProxy->getStorage()->set('event', $event);
    }

    /**
     * @Given /^there is a meeting on spot "(?P<spotReference>[^"]+)"$/
     *
     * @param string $spotReference
     *
     * @return Meeting
     * @throws \Exception
     */
    public function createMeetingOnSpot($spotReference)
    {
        if (!$this->eventContextProxy->getStorage()->get('event')) {
            $this->createEvent();
        }

        $spot = $this->getSpotRepository()->findByReference($this->eventContextProxy->getStorage()->get('event'), $spotReference);

        if (null === $spot) {
            throw new \InvalidArgumentException('Given spot reference not exists');
        }

        $meetingRequest = $this->createMeetinRequest();
        $slots = $this->getMeetingSlotRepository()->findByEvent($this->eventContextProxy->getStorage()->get('event'));

        if (0 === count($slots)) {
            throw new \Exception('There are no available slot for this meeting');
        }

        return $this->createMeetingFromRequest($meetingRequest, reset($slots), $spot);
    }

    /**
     * @param Request     $meetingRequest
     * @param MeetingSlot $slot
     * @param Spot        $spot
     *
     * @return Meeting
     */
    private function createMeetingFromRequest(Request $meetingRequest, MeetingSlot $slot, Spot $spot)
    {
        $meeting = new Meeting(
            $meetingRequest,
            $slot,
            $meetingRequest->getFromSheet(),
            $meetingRequest->getFromParticipants()->toArray(),
            $meetingRequest->getToSheet(),
            $meetingRequest->getToParticipants()->toArray(),
            new \DateTime(),
            $spot,
            false,
            false
        );
        $this->getMeetingRepository()->add($meeting);

        return $meeting;
    }

    /**
     * @return Request
     */
    public function createMeetinRequest()
    {
        $fromUser        = $this->createUser();
        $fromSheet       = $this->createSheet($fromUser);
        $fromParticipant = $this->createParticipant($fromSheet, $fromUser);

        $toUser        = $this->createUser();
        $toSheet       = $this->createSheet($toUser);
        $toParticipant = $this->createParticipant($toSheet, $toUser);

        $meetingRequest = new Request(
            $fromSheet,
            [$fromParticipant],
            $toSheet,
            [$toParticipant],
            new \DateTime(),
            $fromUser
        );
        $this->getMeetingRequestRepository()->add($meetingRequest);

        return $meetingRequest;
    }

    /**
     * @param null $email
     *
     * @return User
     */
    public function createUser($email = null)
    {
        $email = sprintf('%s@example.net', uniqid());
        $user = UserFactory::create($email);
        $this->getUserRepository()->add($user);

        return $user;
    }

    /**
     * @Given there is a sheet
     *
     * @param User|null $user
     * @param Type|null $type
     *
     * @return Sheet
     */
    public function createSheet(User $user = null, Type $type = null)
    {
        if (!$this->eventContextProxy->getStorage()->get('event')) {
            throw new \InvalidArgumentException('Missing event');
        }

        if (null === $user) {
            $user = $this->createUser();
        }

        if (null === $type) {
            $type = $this->createType();
        }

        $sheet = SheetFactory::create($this->lastEvent, $user, new \DateTime(), $type);
        $sheet->setData([]);
        $sheet->setRegistrationData([]);
        $this->getSheetRepository()->add($sheet);

        $this->lastSheet = $sheet;

        return $sheet;
    }

    /**
     * @return Type
     */
    public function createType()
    {
        if (!$this->eventContextProxy->getStorage()->get('event')) {
            throw new \InvalidArgumentException('Missing event');
        }

        $sheetTemplate = $this->createSheetTemplate();
        $registrationTemplate = $this->createRegistrationTemplate();

        $type = new Type($this->lastEvent);
        $type->setSheetTemplate($sheetTemplate);
        $type->setRegistrationTemplate($registrationTemplate);
        $this->getTypeRepository()->add($type);

        return $type;
    }

    /**
     * @return RegistrationTemplate
     */
    private function createRegistrationTemplate()
    {
        /** @var Event|null $event */
        $event = $this->eventContextProxy->getStorage()->get('event');

        if (null === $event) {
            throw new \InvalidArgumentException('Missing event');
        }

        $registrationTemplate = new RegistrationTemplate(
            'RegistrationTemplate',
            [],
            $event->getLocales(),
            $event->getFallback(),
            new \DateTime(),
            $event
        );
        $this->getRegistrationTemplateRepository()->add($registrationTemplate);

        return $registrationTemplate;
    }

    /**
     * @return SheetTemplate
     */
    private function createSheetTemplate()
    {
        if (!$this->storage->getLastEvent()) {
            throw new \InvalidArgumentException('Missing event');
        }

        $sheetTemplate = new SheetTemplate(
            'SheetTemplate',
            [],
            $this->lastEvent->getLocales(),
            $this->lastEvent->getFallback(),
            new \DateTime(),
            [],
            $this->lastEvent
        );
        $this->getSheetTemplateRepository()->add($sheetTemplate);

        return $sheetTemplate;
    }

    /**
     * @param $sheet
     * @param $user
     *
     * @return Participant
     */
    public function createParticipant($sheet, $user)
    {
        $participant = ParticipantFactory::create($sheet, $user);
        $participant->setData([]);
        $this->getParticipantRepository()->add($participant);

        return $participant;
    }
}
