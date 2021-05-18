<?php

namespace Proximum\Vimeet\Tests\Application\Command\Meeting;

use DateTime;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Adapter\MessageBusInterface;
use Proximum\Vimeet\Application\Command\Meeting\EvaluationTimeoutHandler;
use Proximum\Vimeet\Application\Command\Meeting\EvaluationTimeoutMessage;
use Proximum\Vimeet\Application\Components\Worker\TimestampProvider;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Meeting\MeetingEvaluationUpdateExpiredEvent;
use Proximum\Vimeet\Domain\Exception\Meeting\MeetingException;
use Proximum\Vimeet\Domain\Model\Contact;
use Proximum\Vimeet\Domain\Model\Meeting;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\ContactRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\MeetingRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\UserRepositoryInterface;
use Proximum\Vimeet\Tests\Factory\EventFactory;
use Proximum\Vimeet\Tests\Factory\MeetingFactory;
use Proximum\Vimeet\Tests\Factory\SheetFactory;
use Proximum\Vimeet\Tests\Factory\UserFactory;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class EvaluationTimeoutHandlerTest extends TestCase
{
    private ObjectProphecy $contactRepository;
    private ObjectProphecy $meetingRepository;
    private ObjectProphecy $userRepository;
    private ObjectProphecy $messageBus;
    private ObjectProphecy $eventDispatcher;
    private ObjectProphecy $timestampProvider;
    private EvaluationTimeoutHandler $evaluationTimeoutHandler;
    private Meeting $meeting;
    private User $fromUser;
    private DateTime $dateTime;

    protected function setUp()
    {
        // services
        $this->contactRepository = $this->prophesize(ContactRepositoryInterface::class);
        $this->meetingRepository = $this->prophesize(MeetingRepositoryInterface::class);
        $this->userRepository = $this->prophesize(UserRepositoryInterface::class);
        $this->messageBus = $this->prophesize(MessageBusInterface::class);
        $this->eventDispatcher = $this->prophesize(EventDispatcherInterface::class);
        $this->timestampProvider = $this->prophesize(TimestampProvider::class);

        // objects
        $this->fromUser = UserFactory::create('boss@bigcompany.com', 66);
        $event = EventFactory::createEvent();
        $fromSheet = SheetFactory::create($event, $this->fromUser);
        $this->meeting = MeetingFactory::createMeeting($fromSheet, null, $event);
        $this->dateTime = DateTime::createFromFormat('!Y-m-d H:i', '2020-06-01 12:05');
        $this->timestampProvider->getTimestamp()->willReturn($this->dateTime->getTimestamp());

        // handler
        $this->evaluationTimeoutHandler = new EvaluationTimeoutHandler(
            $this->contactRepository->reveal(),
            $this->meetingRepository->reveal(),
            $this->userRepository->reveal(),
            $this->messageBus->reveal(),
            $this->eventDispatcher->reveal(),
            $this->timestampProvider->reveal(),
            null
        );
    }

    public function testHandleDispatchMeetingEvaluationUpdateExpiredEvent()
    {
        $this->meetingRepository->findById(1)->willReturn($this->meeting);
        $fiveMinutesBeforeDateTime = DateTime::createFromFormat('!Y-m-d H:i', '2020-06-01 12:00');
        $this->contactRepository->findLatestEvaluatedAt(1, 66, [21, 22, 23, 24])->willReturn($fiveMinutesBeforeDateTime);
        $this->userRepository->findOneById(66)->willReturn($this->fromUser);
        $contactUser1 = UserFactory::create('alice@provider.com', 21);
        $this->userRepository->findOneById(21)->willReturn($contactUser1);
        $contactUser2 = UserFactory::create('bob@provider.com', 22);
        $this->userRepository->findOneById(22)->willReturn($contactUser2);
        $contactUser3 = UserFactory::create('charly@provider.com', 23);
        $this->userRepository->findOneById(23)->willReturn(null);
        $contactUser4 = UserFactory::create('david@provider.com', 24);
        $this->userRepository->findOneById(24)->willReturn($contactUser4);
        $contact1 = new Contact($this->meeting->getEvent(), $this->fromUser, $contactUser1, $this->dateTime, Contact::ORIGIN_MEETING);
        $contact1->setEvaluation(4, $fiveMinutesBeforeDateTime);
        $contact2 = new Contact($this->meeting->getEvent(), $this->fromUser, $contactUser2, $this->dateTime, Contact::ORIGIN_MEETING);
        $contact2->setEvaluation(5, $fiveMinutesBeforeDateTime);
        $contact3 = new Contact($this->meeting->getEvent(), $this->fromUser, $contactUser3, $this->dateTime, Contact::ORIGIN_MEETING);
        $contact4 = new Contact($this->meeting->getEvent(), $this->fromUser, $contactUser4, $this->dateTime, Contact::ORIGIN_MEETING);
        $this->contactRepository->find(Argument::type(Contact::class))->willReturn($contact1, $contact2);

        $message = new EvaluationTimeoutMessage($this->meeting, $this->fromUser, [$contact1, $contact2, $contact3, $contact4]);
        $this->evaluationTimeoutHandler->handle($message);

        $this->eventDispatcher->dispatch(Events::MEETING_EVALUATION_UPDATE_EXPIRED, new MeetingEvaluationUpdateExpiredEvent(
            $this->meeting,
            $contactUser1,
            $this->meeting->getFromSheet(),
            4
        ))->shouldHaveBeenCalled();
        $this->eventDispatcher->dispatch(Events::MEETING_EVALUATION_UPDATE_EXPIRED, new MeetingEvaluationUpdateExpiredEvent(
            $this->meeting,
            $contactUser2,
            $this->meeting->getFromSheet(),
            5
        ))->shouldHaveBeenCalled();
    }

    public function testPostponeDispatchIfEvaluationHasBeenUpdated()
    {
        $this->meetingRepository->findById(1)->willReturn($this->meeting);
        $threeMinutesBeforeDateTime = DateTime::createFromFormat('!Y-m-d H:i', '2020-06-01 12:02');
        $this->contactRepository->findLatestEvaluatedAt(1, 66, [21])->willReturn($threeMinutesBeforeDateTime);
        $contact1 = new Contact(
            $this->meeting->getEvent(),
            $this->fromUser,
            UserFactory::create('alice@provider.com', 21),
            $this->dateTime,
            Contact::ORIGIN_MEETING
        );

        $message = new EvaluationTimeoutMessage($this->meeting, $this->fromUser, [$contact1]);
        $this->evaluationTimeoutHandler->handle($message);

        $this->messageBus->dispatchDelayed($message, 120)->shouldHaveBeenCalled();
        $this->eventDispatcher->dispatch()->shouldNotHaveBeenCalled();
    }

    public function testThrowExceptionIfFromUserNotFound()
    {
        $this->meetingRepository->findById(1)->willReturn($this->meeting);
        $fiveMinutesBeforeDateTime = DateTime::createFromFormat('!Y-m-d H:i', '2020-06-01 12:00');
        $this->contactRepository->findLatestEvaluatedAt(1, 66, [])->willReturn($fiveMinutesBeforeDateTime);
        $this->userRepository->findOneById(66)->willReturn(null);

        $this->expectException(MeetingException::class);
        $message = new EvaluationTimeoutMessage($this->meeting, $this->fromUser, []);
        $this->evaluationTimeoutHandler->handle($message);
    }
}
