<?php

namespace Proximum\Vimeet\Tests\Ui\Bundle\EventBundle\Handler\Visio;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Meeting;
use Proximum\Vimeet\Domain\Model\MeetingSlot;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\ContactRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\MeetingRepositoryInterface;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Handler\Visio\PreviousMeetingEvaluationChecker;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Handler\Visio\PreviousMeetingEvaluationCheckerHandler;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Routing\RouterInterface;

class PreviousMeetingEvaluationCheckerHandlerTest extends TestCase
{
    /** @var ObjectProphecy */
    private $meetingRepository, $contactRepository, $router, $flashBag, $event, $sheet, $user, $meeting, $type;

    public function setUp(): void
    {
        $this->meetingRepository = $this->prophesize(MeetingRepositoryInterface::class);
        $this->contactRepository = $this->prophesize(ContactRepositoryInterface::class);
        $this->flashBag = $this->prophesize(FlashBagInterface::class);
        $this->router = $this->prophesize(RouterInterface::class);
        $this->event = $this->prophesize(Event::class);
        $this->sheet = $this->prophesize(Sheet::class);
        $this->user = $this->prophesize(User::class);
        $this->meeting = $this->prophesize(Meeting::class);
        $this->type = $this->prophesize(Type::class);
    }

    public function testHandleNotMandatoryToEvaluate(): void
    {
        $this->sheet->getType()->shouldBeCalled()->willReturn($this->type->reveal());
        $this->type->mustEvaluateMeeting()->shouldBeCalled()->willReturn(false);

        $handler = new PreviousMeetingEvaluationCheckerHandler(
            $this->router->reveal(),
            $this->flashBag->reveal(),
            $this->meetingRepository->reveal(),
            $this->contactRepository->reveal()
        );

        $command = new PreviousMeetingEvaluationChecker(
            $this->event->reveal(),
            $this->sheet->reveal(),
            $this->user->reveal(),
            $this->meeting->reveal()
        );
        $handler($command);
    }

    public function testHandleNoPreviousMeeting(): void
    {
        $this->sheet->getType()->shouldBeCalled()->willReturn($this->type->reveal());
        $this->type->mustEvaluateMeeting()->shouldBeCalled()->willReturn(true);

        $participant = $this->prophesize(Participant::class);

        $this->sheet->getUserParticipant($this->user->reveal())
            ->shouldBeCalled()
            ->willReturn($participant->reveal())
        ;

        $slot = $this->prophesize(MeetingSlot::class);
        $begin = new \DateTime();
        $slot->getBegin()->shouldBeCalled()->willReturn($begin);
        $this->meeting->getSlot()->shouldBeCalled()->willReturn($slot->reveal());

        $this->meetingRepository
            ->getPreviousVisioMeeting(
                $this->event->reveal(),
                $this->sheet->reveal(),
                $participant->reveal(),
                $begin
            )
            ->shouldBeCalled()
            ->willReturn(null)
        ;

        $this->contactRepository
            ->hasEvaluateContactByEventAndUser(
                $this->event->reveal(),
                $this->user->reveal(),
                Argument::any()
            )->shouldNotBeCalled()
        ;


        $this->flashBag
            ->add('warning', 'flash.meeting.evaluation.previous_meeting_not_evaluate.warning')
            ->shouldNotBeCalled()
        ;

        $this->router
            ->generate('event_meeting_evaluation', Argument::any())
            ->shouldNotBeCalled()
        ;

        $handler = new PreviousMeetingEvaluationCheckerHandler(
            $this->router->reveal(),
            $this->flashBag->reveal(),
            $this->meetingRepository->reveal(),
            $this->contactRepository->reveal()
        );

        $command = new PreviousMeetingEvaluationChecker(
            $this->event->reveal(),
            $this->sheet->reveal(),
            $this->user->reveal(),
            $this->meeting->reveal()
        );
        $result = $handler($command);

        $this->assertNull($result);
    }

    public function testHandleAlreadyEvaluate(): void
    {
        $this->sheet->getType()->shouldBeCalled()->willReturn($this->type->reveal());
        $this->type->mustEvaluateMeeting()->shouldBeCalled()->willReturn(true);

        $participant = $this->prophesize(Participant::class);

        $this->sheet->getUserParticipant($this->user->reveal())
            ->shouldBeCalled()
            ->willReturn($participant->reveal())
        ;

        $slot = $this->prophesize(MeetingSlot::class);
        $begin = new \DateTime();
        $slot->getBegin()->shouldBeCalled()->willReturn($begin);
        $this->meeting->getSlot()->shouldBeCalled()->willReturn($slot->reveal());

        $previousMeeting = $this->prophesize(Meeting::class);
        $this->meetingRepository
            ->getPreviousVisioMeeting(
                $this->event->reveal(),
                $this->sheet->reveal(),
                $participant->reveal(),
                $begin
            )
            ->shouldBeCalled()
            ->willReturn($previousMeeting->reveal())
        ;

        $metParticipant1 = $this->prophesize(Participant::class);
        $metParticipant2 = $this->prophesize(Participant::class);
        $previousMeeting
            ->getMetParticipants($this->sheet->reveal())
            ->shouldBeCalled()
            ->willReturn([
                $metParticipant1->reveal(),
                $metParticipant2->reveal()
            ])
        ;

        $metUser1 = $this->prophesize(User::class);
        $metUser2 = $this->prophesize(User::class);
        $metParticipant1
            ->getUser()
            ->shouldBeCalled()
            ->willReturn($metUser1->reveal())
        ;
        $metParticipant2
            ->getUser()
            ->shouldBeCalled()
            ->willReturn($metUser2->reveal())
        ;

        $this->contactRepository
            ->hasEvaluateContactByEventAndUser(
                $this->event->reveal(),
                $this->user->reveal(),
                $metUser1->reveal()
            )->shouldBeCalled()
            ->willReturn(true)
        ;

        $this->contactRepository
            ->hasEvaluateContactByEventAndUser(
                $this->event->reveal(),
                $this->user->reveal(),
                $metUser2->reveal()
            )->shouldBeCalled()
            ->willReturn(true)
        ;

        $this->flashBag
            ->add('warning', 'flash.meeting.evaluation.previous_meeting_not_evaluate.warning')
            ->shouldNotBeCalled()
        ;

        $this->router
            ->generate('event_meeting_evaluation', Argument::any())
            ->shouldNotBeCalled()
        ;

        $handler = new PreviousMeetingEvaluationCheckerHandler(
            $this->router->reveal(),
            $this->flashBag->reveal(),
            $this->meetingRepository->reveal(),
            $this->contactRepository->reveal()
        );

        $command = new PreviousMeetingEvaluationChecker(
            $this->event->reveal(),
            $this->sheet->reveal(),
            $this->user->reveal(),
            $this->meeting->reveal()
        );
        $result = $handler($command);

        $this->assertNull($result);
    }

    public function testHandle(): void
    {
        $this->sheet->getType()->shouldBeCalled()->willReturn($this->type->reveal());
        $this->type->mustEvaluateMeeting()->shouldBeCalled()->willReturn(true);

        $participant = $this->prophesize(Participant::class);

        $this->sheet->getUserParticipant($this->user->reveal())
            ->shouldBeCalled()
            ->willReturn($participant->reveal())
        ;

        $slot = $this->prophesize(MeetingSlot::class);
        $begin = new \DateTime();
        $slot->getBegin()->shouldBeCalled()->willReturn($begin);
        $this->meeting->getSlot()->shouldBeCalled()->willReturn($slot->reveal());

        $previousMeeting = $this->prophesize(Meeting::class);
        $this->meetingRepository
            ->getPreviousVisioMeeting(
                $this->event->reveal(),
                $this->sheet->reveal(),
                $participant->reveal(),
                $begin
            )
            ->shouldBeCalled()
            ->willReturn($previousMeeting->reveal())
        ;

        $metParticipant1 = $this->prophesize(Participant::class);
        $metParticipant2 = $this->prophesize(Participant::class);
        $previousMeeting
            ->getMetParticipants($this->sheet->reveal())
            ->shouldBeCalled()
            ->willReturn([
                $metParticipant1->reveal(),
                $metParticipant2->reveal()
            ])
        ;

        $metUser1 = $this->prophesize(User::class);
        $metUser2 = $this->prophesize(User::class);
        $metParticipant1
            ->getUser()
            ->shouldBeCalled()
            ->willReturn($metUser1->reveal())
        ;
        $metParticipant2
            ->getUser()
            ->shouldBeCalled()
            ->willReturn($metUser2->reveal())
        ;

        $this->contactRepository
            ->hasEvaluateContactByEventAndUser(
                $this->event->reveal(),
                $this->user->reveal(),
                $metUser1->reveal()
            )->shouldBeCalled()
            ->willReturn(true)
        ;

        $this->contactRepository
            ->hasEvaluateContactByEventAndUser(
                $this->event->reveal(),
                $this->user->reveal(),
                $metUser2->reveal()
            )->shouldBeCalled()
            ->willReturn(false)
        ;

        $this->flashBag
            ->add('warning', 'flash.meeting.evaluation.previous_meeting_not_evaluate.warning')
            ->shouldBeCalled()
        ;

        $this->sheet->getId()->shouldBeCalled()->willReturn(12);
        $previousMeeting->getId()->shouldBeCalled()->willReturn(11);
        $this->router
            ->generate(
                'event_meeting_evaluation',
                [
                    'sheet' => 12,
                    'meeting' => 11,
                ]
            )->shouldBeCalled()
            ->willReturn('/route/to/redirect')
        ;

        $handler = new PreviousMeetingEvaluationCheckerHandler(
            $this->router->reveal(),
            $this->flashBag->reveal(),
            $this->meetingRepository->reveal(),
            $this->contactRepository->reveal()
        );

        $command = new PreviousMeetingEvaluationChecker(
            $this->event->reveal(),
            $this->sheet->reveal(),
            $this->user->reveal(),
            $this->meeting->reveal()
        );
        $result = $handler($command);

        $this->assertInstanceOf(
            RedirectResponse::class,
            $result
        );
        $this->assertEquals(
            '/route/to/redirect',
            $result->getTargetUrl()
        );
    }
}
