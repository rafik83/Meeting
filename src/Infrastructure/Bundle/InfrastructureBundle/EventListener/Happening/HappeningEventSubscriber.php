<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\EventListener\Happening;

use Proximum\Vimeet\Application\Command\Happening\Webinar\Record\PrepareReconciliation;
use Proximum\Vimeet\Application\Command\Happening\Webinar\Record\PrepareReconciliationHandler;
use Proximum\Vimeet\Application\Components\Happening\Participation\DisableEnableParticipation;
use Proximum\Vimeet\Application\Components\Happening\Participation\UserParticipantAvailabilityReAggregator;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Happening\Created;
use Proximum\Vimeet\Application\Event\Happening\DatesUpdated;
use Proximum\Vimeet\Application\Event\Happening\TypesUpdated;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class HappeningEventSubscriber implements EventSubscriberInterface
{
    /** @var DisableEnableParticipation */
    private $disableEnableParticipation;

    /** @var UserParticipantAvailabilityReAggregator */
    private $participantAvailabilityReAggregator;

    /** @var PrepareReconciliationHandler */
    private $prepareReconciliationHandler;

    public function __construct(
        DisableEnableParticipation $disableEnableParticipation,
        UserParticipantAvailabilityReAggregator $participantAvailabilityReAggregator,
        PrepareReconciliationHandler $prepareReconciliationHandler
    ) {
        $this->disableEnableParticipation = $disableEnableParticipation;
        $this->participantAvailabilityReAggregator = $participantAvailabilityReAggregator;
        $this->prepareReconciliationHandler = $prepareReconciliationHandler;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            Events::HAPPENING_TYPES_UPDATED => 'onTypesUpdated',
            Events::HAPPENING_DATES_UPDATED => 'onDatesUpdated',
            Events::HAPPENING_CREATED => 'onHappeningCreation',
        ];
    }

    public function onHappeningCreation(Created $event): void
    {
        $this->prepareReconciliationHandler->handle(
            new PrepareReconciliation($event->getHappening(), null)
        );
    }

    public function onTypesUpdated(TypesUpdated $event): void
    {
        $this->disableEnableParticipation->resolveParticipations($event->getHappening());
    }

    public function onDatesUpdated(DatesUpdated $event): void
    {
        $this->participantAvailabilityReAggregator->recalculateAvailabilityAggregator($event->getHappening());

        $this->prepareReconciliationHandler->handle(
            new PrepareReconciliation($event->getHappening(), null)
        );
    }
}
