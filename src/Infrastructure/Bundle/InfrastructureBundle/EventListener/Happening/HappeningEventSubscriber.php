<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\EventListener\Happening;

use DateTime;
use Proximum\Vimeet\Application\Command\Happening\Webinar\Record\Download\PlanDownloadRecordArchive;
use Proximum\Vimeet\Application\Command\Happening\Webinar\Record\Download\PlanDownloadRecordArchiveHandler;
use Proximum\Vimeet\Application\Command\Happening\Webinar\Record\PrepareReconciliation;
use Proximum\Vimeet\Application\Command\Happening\Webinar\Record\PrepareReconciliationHandler;
use Proximum\Vimeet\Application\Components\Happening\Participation\DisableEnableParticipation;
use Proximum\Vimeet\Application\Components\Happening\Participation\UserParticipantAvailabilityReAggregator;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Happening\Created;
use Proximum\Vimeet\Application\Event\Happening\DatesUpdated;
use Proximum\Vimeet\Application\Event\Happening\TypesUpdated;
use Proximum\Vimeet\Application\Event\Happening\Webinar\RecordingEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class HappeningEventSubscriber implements EventSubscriberInterface
{
    /** @var DisableEnableParticipation */
    private $disableEnableParticipation;

    /** @var UserParticipantAvailabilityReAggregator */
    private $participantAvailabilityReAggregator;

    /** @var PrepareReconciliationHandler */
    private $prepareReconciliationHandler;

    /** @var PlanDownloadRecordArchiveHandler */
    private $planDownloadRecordArchiveHandler;

    public function __construct(
        DisableEnableParticipation $disableEnableParticipation,
        UserParticipantAvailabilityReAggregator $participantAvailabilityReAggregator,
        PrepareReconciliationHandler $prepareReconciliationHandler,
        PlanDownloadRecordArchiveHandler $planDownloadRecordArchiveHandler
    ) {
        $this->disableEnableParticipation = $disableEnableParticipation;
        $this->participantAvailabilityReAggregator = $participantAvailabilityReAggregator;
        $this->prepareReconciliationHandler = $prepareReconciliationHandler;
        $this->planDownloadRecordArchiveHandler = $planDownloadRecordArchiveHandler;
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
            Events::HAPPENING_RECORDING => 'onHappeningRecording',
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

    public function onHappeningRecording(RecordingEvent $event): void
    {
        $dueDate = new DateTime();
        $dueDate->setTimestamp($event->happening->getEnd()->getTimestamp());
        $dueDate->modify('+15 minutes');

        $this->planDownloadRecordArchiveHandler->handle(
            new PlanDownloadRecordArchive(
                $event->happening,
                $dueDate
            )
        );
    }
}
