<?php

namespace Proximum\Vimeet\Tests\Application\Query\Meeting;

use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Query\Meeting\MeetingRequestListViewQuery;
use Proximum\Vimeet\Application\Query\Meeting\MeetingRequestListViewQueryHandler;
use Proximum\Vimeet\Application\Query\Meeting\MeetingRequestViewQuery;
use Proximum\Vimeet\Application\Query\Meeting\MeetingRequestViewQueryHandler;
use Proximum\Vimeet\Application\Query\Sheet\Viewed\ViewedSheetListViewQuery;
use Proximum\Vimeet\Application\Query\Sheet\Viewed\ViewedSheetListViewQueryHandler;
use Proximum\Vimeet\Application\View\Meeting\MeetingRequestListView;
use Proximum\Vimeet\Application\View\Meeting\MeetingRequestView;
use Proximum\Vimeet\Domain\KeyDates\Checker\AnsweringMeetingRequestAccessChecker;
use Proximum\Vimeet\Domain\KeyDates\Checker\MeetingPublishedAccessChecker;
use Proximum\Vimeet\Domain\KeyDates\Checker\MeetingRequestAccessChecker;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Meeting\Request;
use Proximum\Vimeet\Domain\Model\MeetingSlot;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\Meeting\RequestRepositoryInterface;
use Proximum\Vimeet\Domain\User\Phone\ValidationRequiredChecker;

class MeetingRequestListViewQueryHandlerTest extends TestCase
{
    /** @var ObjectProphecy */
    private $meetingRequestRepository,
        $meetingRequestViewQueryHandler,
        $meetingPublishedAccessChecker,
        $meetingRequestAccessChecker,
        $answeringMeetingRequestAccessChecker,
        $viewedSheetListViewQueryHandler,
        $validationRequiredChecker
    ;

    public function setUp(): void
    {
        $this->meetingRequestRepository = $this->prophesize(RequestRepositoryInterface::class);
        $this->meetingRequestViewQueryHandler = $this->prophesize(MeetingRequestViewQueryHandler::class);
        $this->meetingPublishedAccessChecker = $this->prophesize(MeetingPublishedAccessChecker::class);
        $this->meetingRequestAccessChecker = $this->prophesize(MeetingRequestAccessChecker::class);
        $this->answeringMeetingRequestAccessChecker = $this->prophesize(AnsweringMeetingRequestAccessChecker::class);
        $this->viewedSheetListViewQueryHandler = $this->prophesize(ViewedSheetListViewQueryHandler::class);
        $this->validationRequiredChecker = $this->prophesize(ValidationRequiredChecker::class);
    }

    public function testHandle(): void
    {
        $now = new \DateTime();
        $configuration = $this->prophesize(Event\Configuration::class);
        $event = $this->prophesize(Event::class);
        $event->getConfiguration()->willReturn($configuration->reveal());
        $configuration->isMeetingRequestUpdateLocked()->willReturn(true);

        $type    = new Type($event->reveal());
        $user    = new User('email@email.com', 'salt', 'password', 'fr');
        $userTwo = new User('otheruser@email.com', 'salt', 'password', 'fr');

        $sheet    = new Sheet($event->reveal(), $type, [], $user, $now);
        $sheetTwo = new Sheet($event->reveal(), new Type($event->reveal()), [], $userTwo, $now);

        $reflection  = new \ReflectionClass(Sheet::class);
        $property = $reflection->getProperty('id');
        $property->setAccessible(true);
        $property->setValue($sheet, 1);
        $property->setValue($sheetTwo, 2);
        $property->setAccessible(false);

        $meetingRequest = new Request($sheet, [], $sheetTwo, [], $now, $user, $event->reveal());

        $query = new MeetingRequestListViewQuery($event->reveal(), $sheet, $user, 'fr', [], [], true);

        // Expected
        $meetingRequestListView = new MeetingRequestListView();
        $meetingRequestView     = new MeetingRequestView(
            $sheetTwo,
            '',
            Request::STATE_SENT,
            '',
            $now,
            $meetingRequest,
            []
        );
        $meetingRequestListView->addRequestView($meetingRequestView);

        // Mock
        $this->meetingRequestRepository
            ->getAllRequestBySheet($sheet, [], [])
            ->shouldBeCalled()
            ->willReturn([$meetingRequest]);

        $this->viewedSheetListViewQueryHandler
            ->handle(new ViewedSheetListViewQuery($user, [$meetingRequest->getToSheet()]))
            ->shouldBeCalled()
            ->willReturn([2 => $sheetTwo]);

        $this->meetingRequestViewQueryHandler
            ->handle(new MeetingRequestViewQuery(
                $meetingRequest,
                $sheet,
                $user,
                'fr',
                false,
                true,
                false,
                false,
                true,
                false,
                true
            ))
            ->shouldBeCalled()
            ->willReturn($meetingRequestView);

        $this->meetingPublishedAccessChecker->allowedToAccess($event)->shouldBeCalled()->willReturn(false);
        $this->meetingRequestAccessChecker->allowedToAccess($event)->shouldBeCalled()->willReturn(true);
        $this->answeringMeetingRequestAccessChecker->allowedToAccess($event)->shouldBeCalled()->willReturn(true);
        $this->validationRequiredChecker->handle($sheet, $user)->shouldBeCalled()->willReturn(false);

        $handler = new MeetingRequestListViewQueryHandler(
            $this->meetingRequestRepository->reveal(),
            $this->meetingRequestViewQueryHandler->reveal(),
            $this->viewedSheetListViewQueryHandler->reveal(),
            $this->meetingPublishedAccessChecker->reveal(),
            $this->meetingRequestAccessChecker->reveal(),
            $this->answeringMeetingRequestAccessChecker->reveal(),
            $this->validationRequiredChecker->reveal()
        );

        $handler->handle($query);
    }

    public function testHandleWithClosedMeetingRequest(): void
    {
        $now      = new \DateTime();
        $configuration = $this->prophesize(Event\Configuration::class);
        $event = $this->prophesize(Event::class);
        $event->getConfiguration()->willReturn($configuration->reveal());
        $configuration->isMeetingRequestUpdateLocked()->willReturn(false);

        $type    = new Type($event->reveal());
        $user    = new User('email@email.com', 'salt', 'password', 'fr');
        $userTwo = new User('otheruser@email.com', 'salt', 'password', 'fr');

        $sheet    = new Sheet($event->reveal(), $type, [], $user, $now);
        $sheetTwo = new Sheet($event->reveal(), new Type($event->reveal()), [], $userTwo, $now);
        $reflection  = new \ReflectionClass(Sheet::class);
        $property = $reflection->getProperty('id');
        $property->setAccessible(true);
        $property->setValue($sheet, 1);
        $property->setValue($sheetTwo, 2);
        $property->setAccessible(false);

        $meetingRequest = new Request($sheet, [], $sheetTwo, [], $now, $user, $event->reveal());

        $meetingSlot = $this->prophesize(MeetingSlot::class);

        $query = new MeetingRequestListViewQuery($event->reveal(), $sheet, $user, 'fr', [], [$meetingSlot], false);

        // Expected
        $meetingRequestListView = new MeetingRequestListView();
        $meetingRequestView     = new MeetingRequestView(
            $sheetTwo,
            '',
            Request::STATE_SENT,
            '',
            $now,
            $meetingRequest,
            []
        );
        $meetingRequestListView->addRequestView($meetingRequestView);

        // Mock
        $this->meetingRequestRepository
            ->getAllRequestBySheet($sheet, [], [$meetingSlot])
            ->shouldBeCalled()
            ->willReturn([$meetingRequest]);

        $this->viewedSheetListViewQueryHandler
            ->handle(new ViewedSheetListViewQuery($user, [$meetingRequest->getToSheet()]))
            ->shouldBeCalled()
            ->willReturn([]);

        $this->meetingRequestViewQueryHandler
            ->handle(new MeetingRequestViewQuery(
                $meetingRequest,
                $sheet,
                $user,
                'fr',
                false,
                false,
                true,
                true,
                false,
                false
            ))
            ->shouldBeCalled()
            ->willReturn($meetingRequestView);

        $this->meetingPublishedAccessChecker->allowedToAccess($event)->shouldBeCalled()->willReturn(false);
        $this->meetingRequestAccessChecker->allowedToAccess($event)->shouldBeCalled()->willReturn(false);
        $this->answeringMeetingRequestAccessChecker->allowedToAccess($event)->shouldBeCalled()->willReturn(false);
        $this->validationRequiredChecker->handle($sheet, $user)->shouldBeCalled()->willReturn(false);

        $handler = new MeetingRequestListViewQueryHandler(
            $this->meetingRequestRepository->reveal(),
            $this->meetingRequestViewQueryHandler->reveal(),
            $this->viewedSheetListViewQueryHandler->reveal(),
            $this->meetingPublishedAccessChecker->reveal(),
            $this->meetingRequestAccessChecker->reveal(),
            $this->answeringMeetingRequestAccessChecker->reveal(),
            $this->validationRequiredChecker->reveal()
        );

        $handler->handle($query);
    }
}
