<?php

namespace Proximum\Vimeet\Tests\Application\Command\Token\UserEventToken;

use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Adapter\DelayedEventDispatcherInterface;
use Proximum\Vimeet\Application\Command\Token\UserEventToken\ConfirmAgenda;
use Proximum\Vimeet\Application\Command\Token\UserEventToken\ConfirmAgendaHandler;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Sheet\Agenda\AgendaConfirmationEvent;
use Proximum\Vimeet\Domain\Exception\Token\UserEventToken\UserEventTokenUnexpectedTypeException;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Token\UserEventToken;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\Token\UserEventTokenRepositoryInterface;

class ConfirmAgendaHandlerTest extends TestCase
{
    /** @var \DateTime */
    private $dateTime;

    /** @var ObjectProphecy */
    private $userEventTokenRepository;

    /** @var ObjectProphecy */
    private $delayedEventDispatcher;

    public function setUp()
    {
        $this->userEventTokenRepository = $this->prophesize(UserEventTokenRepositoryInterface::class);
        $this->dateTime = new \DateTime();
        $this->delayedEventDispatcher = $this->prophesize(DelayedEventDispatcherInterface::class);
    }

    public function testHandle()
    {
        $userEventToken = $this->prophesize(UserEventToken::class);
        $userEventToken->isConfirmed()->shouldBeCalled()->willReturn(false);
        $userEventToken->isAgendaConfirmation()->shouldBeCalled()->willReturn(true);
        $userEventToken->confirm($this->dateTime)->shouldBeCalled();

        $event = $this->prophesize(Event::class);
        $user  = $this->prophesize(User::class);
        $userEventToken->getUser()->willReturn($user->reveal());
        $userEventToken->getEvent()->willReturn($event->reveal());

        // Expected
        $this->userEventTokenRepository->set($userEventToken->reveal())->shouldBeCalled();
        $this->delayedEventDispatcher
            ->dispatch(
                Events::USER_AGENDA_CONFIRMED,
                new AgendaConfirmationEvent($event->reveal(), $user->reveal())
            )->shouldBeCalled()
        ;

        // Handler
        $command = new ConfirmAgenda($userEventToken->reveal());
        $handler = new ConfirmAgendaHandler(
            $this->userEventTokenRepository->reveal(),
            $this->delayedEventDispatcher->reveal(),
            $this->dateTime
        );
        $result = $handler->handle($command);
        $this->assertEquals('confirmed', $result);
    }

    public function testHandleUserEventTokenAlreadyConfirmed()
    {
        $dateTime = new \DateTime();
        $userEventToken = $this->prophesize(UserEventToken::class);
        $userEventToken->isConfirmed()->shouldBeCalled()->willReturn(true);
        $userEventToken->confirm($dateTime)->shouldNotBeCalled();

        // Expected
        $this->userEventTokenRepository->set($userEventToken->reveal())->shouldNotBeCalled();

        // Handler
        $command = new ConfirmAgenda($userEventToken->reveal());
        $handler = new ConfirmAgendaHandler(
            $this->userEventTokenRepository->reveal(),
            $this->delayedEventDispatcher->reveal(),
            $this->dateTime
        );
        $result = $handler->handle($command);
        $this->assertEquals('already_confirmed', $result);
    }

    public function testHandleUserEventTokenUnexpectedTypeException()
    {
        $dateTime = new \DateTime();
        $userEventToken = $this->prophesize(UserEventToken::class);
        $userEventToken->isConfirmed()->shouldBeCalled()->willReturn(false);
        $userEventToken->isAgendaConfirmation()->shouldBeCalled()->willReturn(false);
        $userEventToken->confirm($dateTime)->shouldNotBeCalled();

        // Expected
        $this->userEventTokenRepository->set($userEventToken->reveal())->shouldNotBeCalled();

        $this->expectException(UserEventTokenUnexpectedTypeException::class);

        // Handler
        $command = new ConfirmAgenda($userEventToken->reveal());
        $handler = new ConfirmAgendaHandler(
            $this->userEventTokenRepository->reveal(),
            $this->delayedEventDispatcher->reveal(),
            $this->dateTime
        );
        $handler->handle($command);
    }
}
