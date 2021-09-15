<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\EventListener\Sheet\Agenda;

use Proximum\Vimeet\Application\Command\Sheet\Agenda\UpdateAgendaConfirmedStatus;
use Proximum\Vimeet\Application\Command\Sheet\Agenda\UpdateAgendaConfirmedStatusHandler;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Sheet\Agenda\AgendaConfirmationEvent;
use Proximum\Vimeet\Application\Event\User\EventToken\AgendaConfirmationStatusUpdated;
use Proximum\Vimeet\Application\Event\User\EventToken\AgendaConfirmationTokenCreated;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class AgendaConfirmationEventSubscriber implements EventSubscriberInterface
{
    /** @var UpdateAgendaConfirmedStatusHandler */
    private $updateAgendaConfirmedStatusHandler;

    /**
     * @param UpdateAgendaConfirmedStatusHandler $updateAgendaConfirmedStatusHandler
     */
    public function __construct(UpdateAgendaConfirmedStatusHandler $updateAgendaConfirmedStatusHandler)
    {
        $this->updateAgendaConfirmedStatusHandler = $updateAgendaConfirmedStatusHandler;
    }

    /**
     * @param AgendaConfirmationEvent $agendaConfirmationEvent
     */
    public function onAgendaConfirmation(AgendaConfirmationEvent $agendaConfirmationEvent)
    {
        $this->updateAgendaConfirmedStatusHandler->handle(
            new UpdateAgendaConfirmedStatus($agendaConfirmationEvent->getEvent(), $agendaConfirmationEvent->getUser())
        );
    }

    /**
     * @param AgendaConfirmationStatusUpdated $agendaConfirmationStatusUpdated
     */
    public function onAgendaConfirmationStatusUpdated(AgendaConfirmationStatusUpdated $agendaConfirmationStatusUpdated)
    {
        $this->updateAgendaConfirmedStatusHandler->handle(
            new UpdateAgendaConfirmedStatus(
                $agendaConfirmationStatusUpdated->getEvent(),
                $agendaConfirmationStatusUpdated->getUser()
            )
        );
    }

    /**
     * @param AgendaConfirmationTokenCreated $agendaConfirmationTokenCreated
     */
    public function onAgendaConfirmationTokenCreated(AgendaConfirmationTokenCreated $agendaConfirmationTokenCreated)
    {
        $this->updateAgendaConfirmedStatusHandler->handle(
            new UpdateAgendaConfirmedStatus(
                $agendaConfirmationTokenCreated->getEvent(),
                $agendaConfirmationTokenCreated->getUser()
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            Events::USER_AGENDA_CONFIRMED => 'onAgendaConfirmation',
            Events::USER_EVENT_TOKEN_AGENDA_CONFIRMATION_CREATED => 'onAgendaConfirmationTokenCreated',
            Events::USER_AGENDA_CONFIRMATION_STATUS_UPDATED => 'onAgendaConfirmationStatusUpdated',
        ];
    }
}
