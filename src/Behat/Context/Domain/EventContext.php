<?php

namespace Proximum\Vimeet\Behat\Context\Domain;

use Behat\Behat\Context\Context;
use Behat\Symfony2Extension\Context\KernelAwareContext;
use Proximum\Vimeet\Domain\Meeting\Slot\Recipe;
use Proximum\Vimeet\Domain\Meeting\Slot\SlotGenerator;
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
use Proximum\Vimeet\Domain\Repository\EventRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\Meeting\RequestRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\MeetingRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\MeetingSlotRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\ParticipantRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\SpotRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\Template\RegistrationTemplateRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\Template\SheetTemplateRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\TypeRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\UserRepositoryInterface;
use Proximum\Vimeet\Tests\Factory\EventFactory;
use Proximum\Vimeet\Tests\Factory\ParticipantFactory;
use Proximum\Vimeet\Tests\Factory\SheetFactory;
use Proximum\Vimeet\Tests\Factory\UserFactory;
use Symfony\Component\HttpKernel\KernelInterface;

class EventContext implements Context, KernelAwareContext
{
    /** @var KernelInterface */
    private $kernel;

    /** @var null|Event */
    private $lastEventCreated;

    /**
     * @param KernelInterface $kernel
     */
    public function setKernel(KernelInterface $kernel)
    {
        $this->kernel = $kernel;
    }

    /**
     * @Given /^the event "(?P<eventTitle>[^"]+)" is created$/
     *
     * @param string $eventTitle
     */
    public function createEvent($eventTitle = null)
    {
        $event = EventFactory::createEvent($eventTitle);
        $eventRepository = $this->getEventRepository();
        $eventRepository->add($event);
        $this->lastEventCreated = $event;
    }

    /**
     * @Given /^there are (?P<quantity>\d+) slots in this event$/
     *
     * @param int $quantity
     */
    public function createSlots($quantity)
    {
        if (!$this->lastEventCreated) {
            $this->createEvent();
        }

        $interval = 5;
        $duration = 10;

        $now = new \DateTime();
        $begin = new \DateTime(sprintf('%s %s', $now->format('Y-m-d'), '08:00:00'));
        $end = clone $begin;
        $end->add(new \DateInterval(sprintf('PT%sM', $interval * $duration * $quantity)));

        $slots = $this->getSlotGenerator()->generate(
            $this->lastEventCreated,
            [new Recipe($begin, $end, $interval, $duration)]
        );

        foreach ($slots as $slot) {
            $this->getMeetingSlotRepository()->add($slot);
        }
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
        if (!$this->lastEventCreated) {
            $this->createEvent();
        }

        $spot = $this->getSpotRepository()->findByReference($this->lastEventCreated, $spotReference);

        if (null === $spot) {
            throw new \InvalidArgumentException('Given spot reference not exists');
        }

        $meetingRequest = $this->createMeetinRequest();
        $slots = $this->getMeetingSlotRepository()->findByEvent($this->lastEventCreated);

        if (0 === count($slots)) {
            throw new \Exception('There are no available slot for this meeting');
        }

        return $this->createMeetingFromRequest($meetingRequest, reset($slots), $spot);
    }

    /**
     * @Given /^spot "(?P<spotReference>[^"]+)" is assigned to another sheet$/
     *
     * @param string $spotReference
     */
    public function spotIsAssignedToAnotherSheet($spotReference)
    {
        $spot = $this->getSpotRepository()->findByReference($this->lastEventCreated, $spotReference);

        if (null === $spot) {
            throw new \InvalidArgumentException('Given spot reference not exists');
        }

        $sheet = $this->createSheet();
        $sheet->setSpot($spot);
        $this->getSheetRepository()->set($sheet);
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
        $fromUser        = $this->createUser('user-from@example.net');
        $fromSheet       = $this->createSheet($fromUser);
        $fromParticipant = $this->createParticipant($fromSheet, $fromUser);

        $toUser        = $this->createUser('user-to@example.net');
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
        $user = UserFactory::create($email);
        $this->getUserRepository()->add($user);

        return $user;
    }

    /**
     * @param User|null $user
     * @param Type|null $type
     *
     * @return Sheet
     */
    public function createSheet(User $user = null, Type $type = null)
    {
        if (!$this->lastEventCreated) {
            $this->createEvent();
        }

        if (null === $user) {
            $user = $this->createUser();
        }

        if (null === $type) {
            $type = $this->createType();
        }

        $sheet = SheetFactory::create($this->lastEventCreated, $user, new \DateTime(), $type);
        $sheet->setData([]);
        $sheet->setRegistrationData([]);
        $this->getSheetRepository()->add($sheet);

        return $sheet;
    }

    /**
     * @return Type
     */
    public function createType()
    {
        if (!$this->lastEventCreated) {
            $this->createEvent();
        }

        $sheetTemplate = $this->createSheetTemplate();
        $registrationTemplate = $this->createRegistrationTemplate();

        $type = new Type($this->lastEventCreated);
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
        if (!$this->lastEventCreated) {
            $this->createEvent();
        }

        $registrationTemplate = new RegistrationTemplate(
            'RegistrationTemplate',
            [],
            $this->lastEventCreated->getLocales(),
            $this->lastEventCreated->getFallback(),
            new \DateTime(),
            $this->lastEventCreated
        );
        $this->getRegistrationTemplateRepository()->add($registrationTemplate);

        return $registrationTemplate;
    }

    /**
     * @return SheetTemplate
     */
    private function createSheetTemplate()
    {
        if (!$this->lastEventCreated) {
            $this->createEvent();
        }

        $sheetTemplate = new SheetTemplate(
            'SheetTemplate',
            [],
            $this->lastEventCreated->getLocales(),
            $this->lastEventCreated->getFallback(),
            new \DateTime(),
            [],
            $this->lastEventCreated
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

    /**
     * @Given /^there is an active spot "(?P<reference>[^"]+)" with meeting capacity of (?P<meetingCapacity>\d+), seat capacity of (?P<seatCapacity>\d+)$/
     *
     * @param string $reference
     * @param int    $meetingCapacity
     * @param int    $seatCapacity
     *
     * @return Spot
     */
    public function createSpot($reference, $meetingCapacity, $seatCapacity)
    {
        if (!$this->lastEventCreated) {
            $this->createEvent();
        }

        $spot = new Spot($reference, $this->lastEventCreated, 1, $meetingCapacity, $seatCapacity, true);
        $this->getSpotRepository()->add($spot);

        return $spot;
    }

    /**
     * @return SheetRepositoryInterface
     */
    protected function getSheetRepository()
    {
        return $this->getService('vimeet_infrastructure.repository.sheet_repository');
    }

    /**
     * @return UserRepositoryInterface
     */
    protected function getUserRepository()
    {
        return $this->getService('vimeet_infrastructure.repository.user_repository');
    }

    /**
     * @return ParticipantRepositoryInterface
     */
    protected function getParticipantRepository()
    {
        return $this->getService('vimeet_infrastructure.repository.participant_repository');
    }

    /**
     * @return RequestRepositoryInterface
     */
    protected function getMeetingRequestRepository()
    {
        return $this->getService('vimeet_infrastructure.repository.meeting.request_repository');
    }

    /**
     * @return MeetingRepositoryInterface
     */
    protected function getMeetingRepository()
    {
        return $this->getService('vimeet_infrastructure.repository.meeting_repository');
    }

    /**
     * @return MeetingSlotRepositoryInterface
     */
    protected function getMeetingSlotRepository()
    {
        return $this->getService('vimeet_infrastructure.repository.meeting_slot_repository');
    }

    /**
     * @return SlotGenerator
     */
    protected function getSlotGenerator()
    {
        return $this->getService('domain.meeting.slot.slot_generator');
    }

    /**
     * @return SpotRepositoryInterface
     */
    protected function getSpotRepository()
    {
        return $this->getService('vimeet_infrastructure.repository.spot_repository');
    }

    /**
     * @return EventRepositoryInterface
     */
    protected function getEventRepository()
    {
        return $this->getService('vimeet_infrastructure.repository.event_repository');
    }

    /**
     * @return TypeRepositoryInterface
     */
    protected function getTypeRepository()
    {
        return $this->getService('vimeet_infrastructure.repository.type_repository');
    }

    /**
     * @return RegistrationTemplateRepositoryInterface
     */
    protected function getRegistrationTemplateRepository()
    {
        return $this->getService('repository.template.registration_template_repository');
    }

    /**
     * @return SheetTemplateRepositoryInterface
     */
    protected function getSheetTemplateRepository()
    {
        return $this->getService('repository.template.sheet_template_repository');
    }

    /**
     * @return mixed
     */
    protected function getService($serviceName)
    {
        return $this->kernel->getContainer()->get($serviceName);
    }
}
