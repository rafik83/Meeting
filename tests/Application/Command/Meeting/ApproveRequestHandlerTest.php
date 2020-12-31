<?php

namespace Proximum\Vimeet\Tests\Application\Command\Meeting;

use DateTime;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Proximum\Vimeet\Application\Command\Meeting\ApproveRequest;
use Proximum\Vimeet\Application\Command\Meeting\ApproveRequestHandler;
use Proximum\Vimeet\Application\Command\Meeting\Event\TransformRequestIntoMeetingHandler;
use Proximum\Vimeet\Application\Components\Meeting\AllowTransformAutomaticallyRequestIntoMeeting;
use Proximum\Vimeet\Application\Components\Meeting\RequestPermissionManager;
use Proximum\Vimeet\Application\Exception\MeetingRequest\IsNotAllowedToApproveMeetingRequestException;
use Proximum\Vimeet\Application\Query\Meeting\MeetingDDayViewQueryHandler;
use Proximum\Vimeet\Domain\Event\Day\DDayGuesser;
use Proximum\Vimeet\Domain\Model\Meeting\Message;
use Proximum\Vimeet\Domain\Model\Meeting\Request;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\Meeting\MessageRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\Meeting\RequestRepositoryInterface;
use Proximum\Vimeet\Domain\User\Phone\ValidationRequiredChecker;
use Proximum\Vimeet\Infrastructure\Adapter\DelayedEventDispatcher;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class ApproveRequestHandlerTest extends TestCase
{
    public function testHandle()
    {
        $event        = EventFactory::createEvent();
        $type         = new Type($event);
        $user1        = new User('test@test.fr', 'test', 'test', 'fr');
        $user2        = new User('test2@test.fr', 'test', 'test', 'fr');
        $user3        = new User('test3@test.fr', 'test', 'test', 'fr');
        $user4        = new User('test4@test.fr', 'test', 'test', 'fr');
        $dateTime     = new DateTime();
        $sheetTo      = new Sheet($event, $type, [], $user1, $dateTime);
        $sheetFrom    = new Sheet($event, $type, [], $user3, $dateTime);
        $toParticipant3 = $this->createParticipantMock($sheetTo, $user3, 3, $dateTime);
        $toParticipant4 = $this->createParticipantMock($sheetTo, $user4, 4, $dateTime);

        $sheetFrom->getParticipants()->add($this->createParticipantMock($sheetFrom, $user1, 1, $dateTime));
        $sheetFrom->getParticipants()->add($this->createParticipantMock($sheetFrom, $user2, 2, $dateTime));
        $sheetTo->getParticipants()->add($toParticipant3);
        $sheetTo->getParticipants()->add($toParticipant4);

        $participants   = [];
        $participants[] = $this->createParticipantMock($sheetTo, $user3, 3, $dateTime);
        $participants[] = $this->createParticipantMock($sheetTo, $user4, 4, $dateTime);

        $request         = new Request($sheetFrom, [], $sheetTo, [], $dateTime, $user1, $event);
        $expectedRequest = new Request($sheetFrom, [], $sheetTo, $participants, $dateTime, $user1, $event, false, true, false, true);
        $expectedRequest->approve($dateTime);

        $approveRequest = new ApproveRequest($user3, $request, $sheetTo, 'fr');
        $approveRequest->participants = [$toParticipant3, $toParticipant4];
        $approveRequest->description = 'content';
        $approveRequest->toPriority = true;

        $requestRepository = $this->prophesize(RequestRepositoryInterface::class);
        $requestRepository->set($expectedRequest)->shouldBeCalled();

        $message = new Message($request, $sheetTo, 'content', $dateTime);
        $messageRepository = $this->prophesize(MessageRepositoryInterface::class);
        $messageRepository->add($message)->shouldBeCalled();

        $permissionManager = $this->prophesize(RequestPermissionManager::class);
        $permissionManager->isAllowedToApprove($request, $sheetTo)->shouldBeCalled()->willReturn(true);

        $eventDispatcher = $this->prophesize(DelayedEventDispatcher::class);
        $validationRequiredChecker = $this->prophesize(ValidationRequiredChecker::class);
        $transformMeetingIntoRequestHandler = $this->prophesize(TransformRequestIntoMeetingHandler::class);
        $ddayGuesser = $this->prophesize(DDayGuesser::class);
        $meetingDDayViewQueryHandler = $this->prophesize(MeetingDDayViewQueryHandler::class);

        $ddayGuesser->isItDDayAndFeatureEnabled($event)->shouldBeCalled()->willReturn(false);

        $allowTransformAutomaticallyRequestIntoMeeting = $this->prophesize(AllowTransformAutomaticallyRequestIntoMeeting::class);
        $allowTransformAutomaticallyRequestIntoMeeting->__invoke($approveRequest->request)->willReturn(false);

        $validationRequiredChecker
            ->handle(Argument::type(Sheet::class), Argument::type(User::class))
            ->shouldNotBeCalled();

        $handler = new ApproveRequestHandler(
            $allowTransformAutomaticallyRequestIntoMeeting->reveal(),
            $requestRepository->reveal(),
            $messageRepository->reveal(),
            $permissionManager->reveal(),
            $eventDispatcher->reveal(),
            $validationRequiredChecker->reveal(),
            $transformMeetingIntoRequestHandler->reveal(),
            $ddayGuesser->reveal(),
            $meetingDDayViewQueryHandler->reveal(),
            $dateTime
        );
        $handler->handle($approveRequest);
    }

    public function testHandleException()
    {
        $this->expectException(IsNotAllowedToApproveMeetingRequestException::class);

        $event        = EventFactory::createEvent();
        $type         = new Type($event);
        $user1        = new User('test@test.fr', 'test', 'test', 'fr');
        $user2        = new User('test2@test.fr', 'test', 'test', 'fr');
        $user3        = new User('test3@test.fr', 'test', 'test', 'fr');
        $user4        = new User('test4@test.fr', 'test', 'test', 'fr');
        $dateTime     = new DateTime();
        $sheetTo      = new Sheet($event, $type, [], $user1, $dateTime);
        $sheetFrom    = new Sheet($event, $type, [], $user3, $dateTime);
        $toParticipant3 = $this->createParticipantMock($sheetTo, $user3, 3, $dateTime);
        $toParticipant4 = $this->createParticipantMock($sheetTo, $user4, 4, $dateTime);

        $sheetFrom->getParticipants()->add($this->createParticipantMock($sheetFrom, $user1, 1, $dateTime));
        $sheetFrom->getParticipants()->add($this->createParticipantMock($sheetFrom, $user2, 2, $dateTime));
        $sheetTo->getParticipants()->add($toParticipant3);
        $sheetTo->getParticipants()->add($toParticipant4);

        $participants   = [];
        $participants[] = $this->createParticipantMock($sheetTo, $user3, 3, $dateTime);
        $participants[] = $this->createParticipantMock($sheetTo, $user4, 4, $dateTime);

        $request         = new Request($sheetFrom, [], $sheetTo, [], $dateTime, $user1, $event);
        $expectedRequest = new Request($sheetFrom, [], $sheetTo, $participants, $dateTime, $user1, $event);
        $expectedRequest->approve($dateTime);

        $approveRequest = new ApproveRequest($user3, $request, $sheetTo, 'fr');
        $approveRequest->participants = [$toParticipant3, $toParticipant4];
        $approveRequest->description = 'content';
        $approveRequest->toPriority = false;

        $requestRepository = $this->prophesize(RequestRepositoryInterface::class);
        $requestRepository->set($expectedRequest)->shouldNotBeCalled();

        $message = new Message($expectedRequest, $sheetTo, 'content', $dateTime);
        $messageRepository = $this->prophesize(MessageRepositoryInterface::class);
        $messageRepository->add($message)->shouldNotBeCalled();

        $permissionManager = $this->prophesize(RequestPermissionManager::class);
        $permissionManager->isAllowedToApprove($request, $sheetTo)->shouldBeCalled()->willReturn(false);

        $eventDispatcher = $this->prophesize(DelayedEventDispatcher::class);
        $validationRequiredChecker = $this->prophesize(ValidationRequiredChecker::class);
        $transformMeetingIntoRequestHandler = $this->prophesize(TransformRequestIntoMeetingHandler::class);
        $ddayGuesser = $this->prophesize(DDayGuesser::class);
        $meetingDDayViewQueryHandler = $this->prophesize(MeetingDDayViewQueryHandler::class);

        $allowTransformAutomaticallyRequestIntoMeeting = $this->prophesize(AllowTransformAutomaticallyRequestIntoMeeting::class);
        $allowTransformAutomaticallyRequestIntoMeeting->__invoke($approveRequest->request)->willReturn(false);

        $validationRequiredChecker
            ->handle(Argument::type(Sheet::class), Argument::type(User::class))
            ->shouldNotBeCalled();

        $ddayGuesser->isItDDay($event)->shouldNotBeCalled();

        $handler = new ApproveRequestHandler(
            $allowTransformAutomaticallyRequestIntoMeeting->reveal(),
            $requestRepository->reveal(),
            $messageRepository->reveal(),
            $permissionManager->reveal(),
            $eventDispatcher->reveal(),
            $validationRequiredChecker->reveal(),
            $transformMeetingIntoRequestHandler->reveal(),
            $ddayGuesser->reveal(),
            $meetingDDayViewQueryHandler->reveal(),
            $dateTime
        );
        $handler->handle($approveRequest);
    }

    public function createParticipantMock(Sheet $sheet, User $user, $id, \DateTime $datetime)
    {
        $participant = new Participant($sheet, $user, [], false, $datetime);
        $reflection  = new \ReflectionClass(Participant::class);

        $property = $reflection->getProperty('id');
        $property->setAccessible(true);
        $property->setValue($participant, $id);
        $property->setAccessible(false);

        return $participant;
    }
}
