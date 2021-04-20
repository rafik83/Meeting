<?php

namespace Proximum\Vimeet\Tests\Application\Command\Meeting\Event;

use DateTimeInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Adapter\TranslatorInterface;
use Proximum\Vimeet\Application\Command\Meeting\Admin\UpdateSlot;
use Proximum\Vimeet\Application\Command\Meeting\Event\Update;
use Proximum\Vimeet\Application\Command\Meeting\Event\UpdateHandler;
use Proximum\Vimeet\Application\Exception\Meeting\UpdateMeetingException;
use Proximum\Vimeet\Domain\Meeting\CanUpdateMeeting;
use Proximum\Vimeet\Domain\Model\Meeting;
use Proximum\Vimeet\Domain\Model\MeetingSlot;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Repository\MeetingRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\Meeting\MessageRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\Meeting\RequestRepositoryInterface;

class UpdateHandlerTest extends TestCase
{
    private ObjectProphecy
        $canUpdateMeeting,
        $commandBus,
        $translator,
        $messageRepository,
        $meetingRepository,
        $requestRepository,
        $request,
        $sheet,
        $meetingSlot,
        $meeting;

    private DateTimeInterface $dateTime;

    public function setUp()
    {
        $this->canUpdateMeeting = $this->prophesize(CanUpdateMeeting::class);
        $this->commandBus = $this->prophesize(CommandBusInterface::class);
        $this->translator = $this->prophesize(TranslatorInterface::class);
        $this->messageRepository = $this->prophesize(MessageRepositoryInterface::class);
        $this->meetingRepository = $this->prophesize(MeetingRepositoryInterface::class);
        $this->dateTime = new \DateTime();
        $this->sheet = $this->prophesize(Sheet::class);
        $this->meeting = $this->prophesize(Meeting::class);
        $this->meetingSlot = $this->prophesize(MeetingSlot::class);
        $this->requestRepository = $this->prophesize(RequestRepositoryInterface::class);
        $this->request = $this->prophesize(Meeting\Request::class);
        $this->meeting->getRequest()->willReturn($this->request->reveal());
    }

    /**
     * @expectedException \Symfony\Component\Security\Core\Exception\AccessDeniedException
     */
    public function testHandleAccessDenied(): void
    {
        $this->canUpdateMeeting->isSatisfiedBy($this->sheet->reveal())
            ->shouldBeCalled()
            ->willReturn(false);

        $this->commandBus->handle(Argument::any())->shouldNotBeCalled();
        $this->messageRepository->add(Argument::any())->shouldNotBeCalled();
        $this->meetingRepository->set(Argument::any())->shouldNotBeCalled();
        $this->requestRepository->set(Argument::any())->shouldNotBeCalled();
        $this->translator->trans(Argument::any())->shouldNotBeCalled();

        $handler = new UpdateHandler(
            $this->canUpdateMeeting->reveal(),
            $this->commandBus->reveal(),
            $this->translator->reveal(),
            $this->messageRepository->reveal(),
            $this->meetingRepository->reveal(),
            $this->dateTime,
            $this->requestRepository->reveal()
        );
        $handler->handle(new Update($this->sheet->reveal(), $this->meeting->reveal(), [], $this->meetingSlot->reveal()));
    }

    public function testHandleUpdateMeetingExceptionIfNoParticipantSelected(): void
    {
        $this->expectException(UpdateMeetingException::class);

        $this->meeting->hasSheet($this->sheet->reveal())->shouldBeCalled()->willReturn(true);

        $this->canUpdateMeeting->isSatisfiedBy($this->sheet->reveal())
            ->shouldBeCalled()
            ->willReturn(true);

        $this->messageRepository->add(Argument::any())->shouldNotBeCalled();
        $this->meetingRepository->set(Argument::any())->shouldNotBeCalled();
        $this->requestRepository->set(Argument::any())->shouldNotBeCalled();
        $this->meeting->setParticipants(Argument::any())->shouldNotBeCalled();

        $handler = new UpdateHandler(
            $this->canUpdateMeeting->reveal(),
            $this->commandBus->reveal(),
            $this->translator->reveal(),
            $this->messageRepository->reveal(),
            $this->meetingRepository->reveal(),
            $this->dateTime,
            $this->requestRepository->reveal()
        );

        $move = new Update($this->sheet->reveal(), $this->meeting->reveal(), [], $this->meetingSlot->reveal());

        $handler->handle($move);
    }

    public function testHandleUpdateMeetingExceptionOnUpdateSlot(): void
    {
        $this->expectException(UpdateMeetingException::class);

        $this->canUpdateMeeting->isSatisfiedBy($this->sheet->reveal())
            ->shouldBeCalled()
            ->willReturn(true);

        $this->meeting->hasSheet($this->sheet->reveal())->shouldBeCalled()->willReturn(true);
        $this->meeting->isVisio()->shouldBeCalled()->willReturn(false);
        $this->meeting->setParticipants($this->sheet->reveal(), Argument::any())->shouldBeCalled();

        $this->commandBus->handle(new UpdateSlot($this->meeting->reveal(), $this->meetingSlot->reveal(), false, true))
            ->shouldBeCalled()
            ->willThrow(new \Exception());
        $this->messageRepository->add(Argument::any())->shouldNotBeCalled();
        $this->meetingRepository->set(Argument::any())->shouldNotBeCalled();
        $this->requestRepository->set(Argument::any())->shouldNotBeCalled();

        $handler = new UpdateHandler(
            $this->canUpdateMeeting->reveal(),
            $this->commandBus->reveal(),
            $this->translator->reveal(),
            $this->messageRepository->reveal(),
            $this->meetingRepository->reveal(),
            $this->dateTime,
            $this->requestRepository->reveal()
        );

        $update = new Update(
            $this->sheet->reveal(),
            $this->meeting->reveal(),
            [$this->prophesize(Participant::class)],
            $this->meetingSlot->reveal()
        );

        $handler->handle($update);
    }

    public function testHandleWithoutContent(): void
    {
        $this->meeting->hasSheet($this->sheet->reveal())->shouldBeCalled()->willReturn(true);

        $this->canUpdateMeeting->isSatisfiedBy($this->sheet->reveal())
            ->shouldBeCalled()
            ->willReturn(true);

        $this->meeting->isVisio()->shouldBeCalled()->willReturn(false);
        $this->meeting->blockSlot()->shouldBeCalled();

        $this->request->setUpdateOrDeleteReasonMessage(null)->shouldBeCalled();

        $this->commandBus->handle(new UpdateSlot($this->meeting->reveal(), $this->meetingSlot->reveal(), false, true))
            ->shouldBeCalled();
        $this->messageRepository->add(Argument::any())->shouldNotBeCalled();
        $this->meetingRepository->set($this->meeting->reveal())->shouldBeCalled();
        $this->requestRepository->set($this->request->reveal())->shouldBeCalled();

        $participant1 = $this->prophesize(Participant::class);
        $participant2 = $this->prophesize(Participant::class);
        $selectedParticipants = [$participant1->reveal(), $participant2->reveal()];

        $this->meeting->setParticipants($this->sheet->reveal(), $selectedParticipants)->shouldBeCalled();

        $handler = new UpdateHandler(
            $this->canUpdateMeeting->reveal(),
            $this->commandBus->reveal(),
            $this->translator->reveal(),
            $this->messageRepository->reveal(),
            $this->meetingRepository->reveal(),
            $this->dateTime,
            $this->requestRepository->reveal()
        );

        $move = new Update($this->sheet->reveal(), $this->meeting->reveal(), $selectedParticipants, $this->meetingSlot->reveal());

        $handler->handle($move);
    }

    public function testHandleWithContent(): void
    {
        $this->meeting->hasSheet($this->sheet->reveal())->shouldBeCalled()->willReturn(true);

        $this->canUpdateMeeting->isSatisfiedBy($this->sheet->reveal())
            ->shouldBeCalled()
            ->willReturn(true);

        $this->meeting->isVisio()->shouldBeCalled()->willReturn(true);
        $this->meeting->blockSlot()->shouldBeCalled();

        $this->request->setUpdateOrDeleteReasonMessage(Argument::type(Meeting\Message::class))->shouldBeCalled();

        $this->commandBus->handle(new UpdateSlot($this->meeting->reveal(), $this->meetingSlot->reveal(), true, true))
            ->shouldBeCalled();

        $message = new Meeting\Message($this->request->reveal(), $this->sheet->reveal(), 'content', $this->dateTime);
        $this->messageRepository->add($message)->shouldBeCalled();
        $this->meetingRepository->set($this->meeting->reveal())->shouldBeCalled();
        $this->requestRepository->set($this->request->reveal())->shouldBeCalled();

        $participant1 = $this->prophesize(Participant::class);
        $participant2 = $this->prophesize(Participant::class);
        $selectedParticipants = [$participant1->reveal(), $participant2->reveal()];

        $this->meeting->setParticipants($this->sheet->reveal(), $selectedParticipants)->shouldBeCalled();

        $handler = new UpdateHandler(
            $this->canUpdateMeeting->reveal(),
            $this->commandBus->reveal(),
            $this->translator->reveal(),
            $this->messageRepository->reveal(),
            $this->meetingRepository->reveal(),
            $this->dateTime,
            $this->requestRepository->reveal()
        );

        $move = new Update($this->sheet->reveal(), $this->meeting->reveal(), $selectedParticipants, $this->meetingSlot->reveal());
        $move->content = 'content';

        $handler->handle($move);
    }
}
