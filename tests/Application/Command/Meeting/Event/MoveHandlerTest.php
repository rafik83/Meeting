<?php

namespace Proximum\Vimeet\Tests\Application\Command\Meeting\Event;

use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Adapter\TranslatorInterface;
use Proximum\Vimeet\Application\Command\Meeting\Admin\UpdateSlot;
use Proximum\Vimeet\Application\Command\Meeting\Event\Move;
use Proximum\Vimeet\Application\Command\Meeting\Event\MoveHandler;
use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Domain\Meeting\CanMoveMeeting;
use Proximum\Vimeet\Domain\Model\Meeting;
use Proximum\Vimeet\Domain\Model\MeetingSlot;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Repository\Meeting\MessageRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\MeetingRepositoryInterface;

class MoveHandlerTest extends TestCase
{
    /** @var ObjectProphecy */
    private
        $canMoveMeeting,
        $commandBus,
        $translator,
        $messageRepository,
        $meetingRepository,
        $datetime,
        $sheet,
        $meetingSlot,
        $meeting;

    public function setUp()
    {
        $this->canMoveMeeting = $this->prophesize(CanMoveMeeting::class);
        $this->commandBus = $this->prophesize(CommandBusInterface::class);
        $this->translator = $this->prophesize(TranslatorInterface::class);
        $this->messageRepository = $this->prophesize(MessageRepositoryInterface::class);
        $this->meetingRepository = $this->prophesize(MeetingRepositoryInterface::class);
        $this->datetime = new \DateTime();
        $this->sheet = $this->prophesize(Sheet::class);
        $this->meeting = $this->prophesize(Meeting::class);
        $this->meetingSlot = $this->prophesize(MeetingSlot::class);
    }

    /**
     * @expectedException \Symfony\Component\Security\Core\Exception\AccessDeniedException
     */
    public function testHandleAccessDenied(): void
    {
        $this->canMoveMeeting->isSatisfiedBy($this->sheet->reveal())
            ->shouldBeCalled()
            ->willReturn(false);

        $this->commandBus->handle(Argument::any())->shouldNotBeCalled();
        $this->messageRepository->add(Argument::any())->shouldNotBeCalled();
        $this->meetingRepository->set(Argument::any())->shouldNotBeCalled();
        $this->translator->trans(Argument::any())->shouldNotBeCalled();

        $handler = new MoveHandler(
            $this->canMoveMeeting->reveal(),
            $this->commandBus->reveal(),
            $this->translator->reveal(),
            $this->messageRepository->reveal(),
            $this->meetingRepository->reveal(),
            $this->datetime
        );
        $handler->handle(new Move($this->sheet->reveal(), $this->meeting->reveal()));
    }

    /**
     * @expectedException \Proximum\Vimeet\Application\Exception\Meeting\MoveMeetingException
     */
    public function testHandleMoveMeetingException(): void
    {
        $this->meeting->hasSheet($this->sheet->reveal())->shouldBeCalled()->willReturn(true);

        $this->canMoveMeeting->isSatisfiedBy($this->sheet->reveal())
            ->shouldBeCalled()
            ->willReturn(true);

        $this->meeting->isVisio()->shouldBeCalled()->willReturn(false);

        $this->commandBus->handle(new UpdateSlot($this->meeting->reveal(), $this->meetingSlot->reveal(), false, true))
            ->shouldBeCalled()
            ->willThrow(new \Exception());
        $this->messageRepository->add(Argument::any())->shouldNotBeCalled();
        $this->meetingRepository->set(Argument::any())->shouldNotBeCalled();

        $handler = new MoveHandler(
            $this->canMoveMeeting->reveal(),
            $this->commandBus->reveal(),
            $this->translator->reveal(),
            $this->messageRepository->reveal(),
            $this->meetingRepository->reveal(),
            $this->datetime
        );

        $move = new Move($this->sheet->reveal(), $this->meeting->reveal());
        $move->meetingSlot = $this->meetingSlot->reveal();

        $handler->handle($move);
    }

    public function testHandleWithoutContent(): void
    {
        $this->meeting->hasSheet($this->sheet->reveal())->shouldBeCalled()->willReturn(true);

        $this->canMoveMeeting->isSatisfiedBy($this->sheet->reveal())
            ->shouldBeCalled()
            ->willReturn(true);

        $this->meeting->isVisio()->shouldBeCalled()->willReturn(false);
        $this->meeting->blockSlot()->shouldBeCalled();

        $this->commandBus->handle(new UpdateSlot($this->meeting->reveal(), $this->meetingSlot->reveal(), false, true))
            ->shouldBeCalled();
        $this->messageRepository->add(Argument::any())->shouldNotBeCalled();
        $this->meetingRepository->set($this->meeting->reveal())->shouldBeCalled();

        $handler = new MoveHandler(
            $this->canMoveMeeting->reveal(),
            $this->commandBus->reveal(),
            $this->translator->reveal(),
            $this->messageRepository->reveal(),
            $this->meetingRepository->reveal(),
            $this->datetime
        );

        $move = new Move($this->sheet->reveal(), $this->meeting->reveal());
        $move->meetingSlot = $this->meetingSlot->reveal();

        $handler->handle($move);
    }

    public function testHandleWithContent(): void
    {
        $this->meeting->hasSheet($this->sheet->reveal())->shouldBeCalled()->willReturn(true);

        $this->canMoveMeeting->isSatisfiedBy($this->sheet->reveal())
            ->shouldBeCalled()
            ->willReturn(true);

        $this->meeting->isVisio()->shouldBeCalled()->willReturn(true);
        $this->meeting->blockSlot()->shouldBeCalled();

        $this->commandBus->handle(new UpdateSlot($this->meeting->reveal(), $this->meetingSlot->reveal(), true, true))
            ->shouldBeCalled();

        $request = $this->prophesize(Meeting\Request::class);
        $this->meeting->getRequest()->shouldBeCalled()->willReturn($request->reveal());

        $message = new Meeting\Message($request->reveal(), $this->sheet->reveal(), 'content', $this->datetime);
        $this->messageRepository->add($message)->shouldBeCalled();
        $this->meetingRepository->set($this->meeting->reveal())->shouldBeCalled();

        $handler = new MoveHandler(
            $this->canMoveMeeting->reveal(),
            $this->commandBus->reveal(),
            $this->translator->reveal(),
            $this->messageRepository->reveal(),
            $this->meetingRepository->reveal(),
            $this->datetime
        );

        $move = new Move($this->sheet->reveal(), $this->meeting->reveal());
        $move->meetingSlot = $this->meetingSlot->reveal();
        $move->content = 'content';

        $handler->handle($move);
    }
}
