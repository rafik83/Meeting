<?php


namespace Proximum\Vimeet\Tests\Application\Command\Meeting\Event;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Adapter\DelayedEventDispatcherInterface;
use Proximum\Vimeet\Application\Command\Meeting\Event\Remove;
use Proximum\Vimeet\Application\Command\Meeting\Event\RemoveHandler;
use Proximum\Vimeet\Domain\Meeting\CanRemoveMeeting;
use Proximum\Vimeet\Domain\Model\Meeting;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Repository\Meeting\RequestRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\MeetingRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\Meeting\MessageRepositoryInterface;

class RemoveHandlerTest extends TestCase
{
    /** @var ObjectProphecy */
    private
        $canRemoveMeeting,
        $messageRepository,
        $meetingRepository,
        $delayedEventDispatcher,
        $datetime,
        $sheet,
        $meeting;

    /** @var ObjectProphecy|RequestRepositoryInterface */
    private $requestRepository;
    /** @var ObjectProphecy|Meeting\Request */
    private $request;

    public function setUp()
    {
        $this->canRemoveMeeting = $this->prophesize(CanRemoveMeeting::class);
        $this->messageRepository = $this->prophesize(MessageRepositoryInterface::class);
        $this->meetingRepository = $this->prophesize(MeetingRepositoryInterface::class);
        $this->delayedEventDispatcher = $this->prophesize(DelayedEventDispatcherInterface::class);
        $this->datetime = new \DateTime();
        $this->sheet = $this->prophesize(Sheet::class);
        $this->meeting = $this->prophesize(Meeting::class);
        $this->requestRepository = $this->prophesize(RequestRepositoryInterface::class);
        $this->request = $this->prophesize(Meeting\Request::class);
        $this->meeting->getRequest()->willReturn($this->request->reveal());
    }

    /**
     * @expectedException \Symfony\Component\Security\Core\Exception\AccessDeniedException
     */
    public function testHandleAccessDenied(): void
    {
        $this->canRemoveMeeting->isSatisfiedBy($this->sheet->reveal())
            ->shouldBeCalled()
            ->willReturn(false);

        $this->messageRepository->add(Argument::any())->shouldNotBeCalled();
        $this->meetingRepository->set(Argument::any())->shouldNotBeCalled();
        $this->requestRepository->set(Argument::any())->shouldNotBeCalled();

        $handler = new RemoveHandler(
            $this->messageRepository->reveal(),
            $this->meetingRepository->reveal(),
            $this->delayedEventDispatcher->reveal(),
            $this->canRemoveMeeting->reveal(),
            $this->datetime,
            $this->requestRepository->reveal()
        );
        $handler->handle(new Remove($this->sheet->reveal(), $this->meeting->reveal()));
    }

    /**
     * @expectedException \Proximum\Vimeet\Application\Exception\Meeting\RemoveMeetingException
     */
    public function testHandleRemoveMeetingException(): void
    {
        $this->meeting->hasSheet($this->sheet->reveal())->shouldBeCalled()->willReturn(true);

        $this->canRemoveMeeting->isSatisfiedBy($this->sheet->reveal())
            ->shouldBeCalled()
            ->willReturn(true);

        $this->messageRepository->add(Argument::any())->shouldNotBeCalled();
        $this->meetingRepository->remove($this->meeting->reveal())->shouldBeCalled()->willThrow(new \Exception());
        $this->requestRepository->set(Argument::any())->shouldNotBeCalled();

        $handler = new RemoveHandler(
            $this->messageRepository->reveal(),
            $this->meetingRepository->reveal(),
            $this->delayedEventDispatcher->reveal(),
            $this->canRemoveMeeting->reveal(),
            $this->datetime,
            $this->requestRepository->reveal()
        );

        $remove = new Remove($this->sheet->reveal(), $this->meeting->reveal());

        $handler->handle($remove);
    }

    /**
     * @throws \Proximum\Vimeet\Application\Exception\Meeting\RemoveMeetingException
     */
    public function testHandleWithoutContent(): void
    {
        $this->meeting->hasSheet($this->sheet->reveal())->shouldBeCalled()->willReturn(true);
        $this->meeting->getFromSheet()->shouldBeCalled();
        $this->meeting->getToSheet()->shouldBeCalled();
        $this->meeting->getAllParticipants()->shouldBeCalled()->willReturn([]);

        $this->canRemoveMeeting->isSatisfiedBy($this->sheet->reveal())
            ->shouldBeCalled()
            ->willReturn(true);

        $this->messageRepository->add(Argument::any())->shouldNotBeCalled();
        $this->meetingRepository->remove($this->meeting->reveal())->shouldBeCalled();

        $handler = new RemoveHandler(
            $this->messageRepository->reveal(),
            $this->meetingRepository->reveal(),
            $this->delayedEventDispatcher->reveal(),
            $this->canRemoveMeeting->reveal(),
            $this->datetime,
            $this->requestRepository->reveal()
        );

        $remove = new Remove($this->sheet->reveal(), $this->meeting->reveal());

        $handler->handle($remove);
    }

    /**
     * @throws \Proximum\Vimeet\Application\Exception\Meeting\RemoveMeetingException
     */
    public function testHandleWithContent(): void
    {
        $this->meeting->hasSheet($this->sheet->reveal())->shouldBeCalled()->willReturn(true);
        $this->meeting->getFromSheet()->shouldBeCalled();
        $this->meeting->getToSheet()->shouldBeCalled();
        $this->meeting->getAllParticipants()->shouldBeCalled()->willReturn([]);;

        $this->request->setUpdateOrDeleteReasonMessage(Argument::type(Meeting\Message::class))->shouldBeCalled();

        $this->canRemoveMeeting->isSatisfiedBy($this->sheet->reveal())
            ->shouldBeCalled()
            ->willReturn(true);

        $message = new Meeting\Message($this->request->reveal(), $this->sheet->reveal(), 'content', $this->datetime);
        $this->messageRepository->add($message)->shouldBeCalled();
        $this->meetingRepository->remove($this->meeting->reveal())->shouldBeCalled();
        $this->requestRepository->set($this->request->reveal())->shouldBeCalled();

        $handler = new RemoveHandler(
            $this->messageRepository->reveal(),
            $this->meetingRepository->reveal(),
            $this->delayedEventDispatcher->reveal(),
            $this->canRemoveMeeting->reveal(),
            $this->datetime,
            $this->requestRepository->reveal()
        );

        $remove = new Remove($this->sheet->reveal(), $this->meeting->reveal());
        $remove->content = 'content';

        $handler->handle($remove);
    }
}
