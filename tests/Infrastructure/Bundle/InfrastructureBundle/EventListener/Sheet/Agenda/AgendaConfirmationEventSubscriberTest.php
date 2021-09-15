<?php

namespace Proximum\Vimeet\Tests\Infrastructure\Bundle\InfrastructureBundle\EventListener\Sheet\Agenda;

use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Command\Sheet\Agenda\UpdateAgendaConfirmedStatus;
use Proximum\Vimeet\Application\Command\Sheet\Agenda\UpdateAgendaConfirmedStatusHandler;
use Proximum\Vimeet\Application\Event\Sheet\Agenda\AgendaConfirmationEvent;
use Proximum\Vimeet\Application\Event\User\EventToken\AgendaConfirmationTokenCreated;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\EventListener\Sheet\Agenda\AgendaConfirmationEventSubscriber;

class AgendaConfirmationEventSubscriberTest extends TestCase
{
    /** @var ObjectProphecy */
    private $updateAgendaConfirmedStatusHandler;

    /** @var ObjectProphecy */
    private $event;

    /** @var ObjectProphecy */
    private $user;

    public function setUp()
    {
        $this->event = $this->prophesize(Event::class);
        $this->user = $this->prophesize(User::class);
        $this->updateAgendaConfirmedStatusHandler = $this->prophesize(UpdateAgendaConfirmedStatusHandler::class);
    }

    public function testOnAgendaConfirmationTokenCreated()
    {
        $agendaConfirmationTokenCreated = new AgendaConfirmationTokenCreated(
            $this->event->reveal(),
            $this->user->reveal()
        );

        // Expected
        $this->updateAgendaConfirmedStatusHandler
            ->handle(new UpdateAgendaConfirmedStatus($this->event->reveal(), $this->user->reveal()))
            ->shouldBeCalled()
        ;

        // AgendaConfirmationEventSubscriber
        $agendaConfirmationEventSubscriber = new AgendaConfirmationEventSubscriber(
            $this->updateAgendaConfirmedStatusHandler->reveal()
        );
        $agendaConfirmationEventSubscriber->onAgendaConfirmationTokenCreated($agendaConfirmationTokenCreated);
    }

    public function testOnAgendaConfirmation()
    {
        $agendaConfirmationEvent = new AgendaConfirmationEvent($this->event->reveal(), $this->user->reveal());

        // Expected
        $this->updateAgendaConfirmedStatusHandler
            ->handle(new UpdateAgendaConfirmedStatus($this->event->reveal(), $this->user->reveal()))
            ->shouldBeCalled()
        ;

        // AgendaConfirmationEventSubscriber
        $agendaConfirmationEventSubscriber = new AgendaConfirmationEventSubscriber(
            $this->updateAgendaConfirmedStatusHandler->reveal()
        );
        $agendaConfirmationEventSubscriber->onAgendaConfirmation($agendaConfirmationEvent);
    }
}
