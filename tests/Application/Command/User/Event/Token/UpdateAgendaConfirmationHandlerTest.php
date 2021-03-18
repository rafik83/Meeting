<?php

namespace Proximum\Vimeet\Tests\Application\Command\User\Event\Token;

use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Adapter\DelayedEventDispatcherInterface;
use Proximum\Vimeet\Application\Command\User\Event\Token\UpdateAgendaConfirmation;
use Proximum\Vimeet\Application\Command\User\Event\Token\UpdateAgendaConfirmationHandler;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\User\EventToken\AgendaConfirmationStatusUpdated;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Token\UserEventToken;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\Token\UserEventTokenRepositoryInterface;
use Proximum\Vimeet\Domain\Token\UserEventTokenGenerator;
use Proximum\Vimeet\Domain\Token\UserEventTokenType;
use Proximum\Vimeet\Domain\User\Event\AgendaConfirmation\Constant;

class UpdateAgendaConfirmationHandlerTest extends TestCase
{
    /** @var ObjectProphecy */
    private $event;

    /** @var ObjectProphecy */
    private $user;

    /** @var ObjectProphecy */
    private $userEventTokenRepository;

    /** @var ObjectProphecy */
    private $userEventTokenGenerator;

    /** @var \DateTime */
    private $dateTime;

    /** @var ObjectProphecy */
    private $delayedEventDispatcher;

    public function setUp()
    {
        $this->event = $this->prophesize(Event::class);
        $this->user = $this->prophesize(User::class);
        $this->userEventTokenRepository = $this->prophesize(UserEventTokenRepositoryInterface::class);
        $this->userEventTokenGenerator = $this->prophesize(UserEventTokenGenerator::class);
        $this->dateTime = new \DateTime();
        $this->delayedEventDispatcher = $this->prophesize(DelayedEventDispatcherInterface::class);
    }

    public function testHandleConfirmed()
    {
        $state = Constant::AGENDA_CONFIRMED;

        $token = $this->prophesize(UserEventToken::class);
        $this->userEventTokenGenerator
            ->getUserEventTokenForConfirmAgenda(
                $this->event->reveal(),
                $this->user->reveal(),
                UserEventTokenType::AGENDA_CONFIRMATION
            )->shouldBeCalled()
            ->willReturn($token)
        ;
        $token->confirm($this->dateTime)->shouldBeCalled();
        $this->userEventTokenRepository->set($token->reveal())->shouldBeCalled();
        $this->delayedEventDispatcher
            ->dispatch(
                Events::USER_AGENDA_CONFIRMATION_STATUS_UPDATED,
                new AgendaConfirmationStatusUpdated($this->event->reveal(), $this->user->reveal())
            )->shouldBeCalled();

        $handler = new UpdateAgendaConfirmationHandler(
            $this->delayedEventDispatcher->reveal(),
            $this->userEventTokenRepository->reveal(),
            $this->userEventTokenGenerator->reveal(),
            $this->dateTime
        );
        $handler->handle(new UpdateAgendaConfirmation($this->event->reveal(), $this->user->reveal(), $state));
    }

    public function testHandleNotConfirmed()
    {
        $state = Constant::AGENDA_NOT_CONFIRMED;

        $token = $this->prophesize(UserEventToken::class);
        $this->userEventTokenGenerator
            ->getUserEventTokenForConfirmAgenda(
                $this->event->reveal(),
                $this->user->reveal(),
                UserEventTokenType::AGENDA_CONFIRMATION
            )->shouldBeCalled()
            ->willReturn($token)
        ;
        $token->unConfirm()->shouldBeCalled();
        $this->userEventTokenRepository->set($token->reveal())->shouldBeCalled();
        $this->delayedEventDispatcher
            ->dispatch(
                Events::USER_AGENDA_CONFIRMATION_STATUS_UPDATED,
                new AgendaConfirmationStatusUpdated($this->event->reveal(), $this->user->reveal())
            )->shouldBeCalled();

        $handler = new UpdateAgendaConfirmationHandler(
            $this->delayedEventDispatcher->reveal(),
            $this->userEventTokenRepository->reveal(),
            $this->userEventTokenGenerator->reveal(),
            $this->dateTime
        );
        $handler->handle(new UpdateAgendaConfirmation($this->event->reveal(), $this->user->reveal(), $state));
    }
}
