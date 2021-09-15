<?php

namespace Proximum\Vimeet\Application\Command\Happening\Webinar\Record;

use Proximum\Vimeet\Application\Adapter\DelayedEventDispatcherInterface;
use Proximum\Vimeet\Application\Adapter\VideoConferenceAdapterInterface;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Happening\Webinar\RecordingEvent;
use Proximum\Vimeet\Domain\Model\Happening\Webinar\RecordArchive;
use Proximum\Vimeet\Domain\Repository\Happening\Webinar\RecordArchiveRepositoryInterface;
use Psr\Log\LoggerInterface;

class RecordHandler
{
    /** @var VideoConferenceAdapterInterface */
    private $videoConferenceAdapter;

    /** @var \DateTimeInterface */
    private $dateTime;

    /** @var RecordArchiveRepositoryInterface */
    private $recordArchiveRepository;

    /** @var PrepareReconciliationHandler */
    private $prepareReconciliationHandler;

    /** @var LoggerInterface */
    private $logger;

    /** @var DelayedEventDispatcherInterface */
    private $delayedEventDispatcher;

    public function __construct(
        VideoConferenceAdapterInterface $videoConferenceAdapter,
        RecordArchiveRepositoryInterface $recordArchiveRepository,
        PrepareReconciliationHandler $prepareReconciliationHandler,
        \DateTimeInterface $dateTime,
        LoggerInterface $logger,
        DelayedEventDispatcherInterface $delayedEventDispatcher
    ) {
        $this->videoConferenceAdapter = $videoConferenceAdapter;
        $this->recordArchiveRepository = $recordArchiveRepository;
        $this->prepareReconciliationHandler = $prepareReconciliationHandler;
        $this->dateTime = $dateTime;
        $this->logger = $logger;
        $this->delayedEventDispatcher = $delayedEventDispatcher;
    }

    public function handle(Record $record): void
    {
        $happening = $record->happening;
        $event = $happening->getEvent();

        if ($this->videoConferenceAdapter->isRecording($happening->getWebinarSessionId())) {
            $this->logger->warning(sprintf('Webinar #%d: Start record failed because another archive is already started', $happening->getId()));

            $this->prepareReconciliationHandler->handle(
                new PrepareReconciliation(
                    $happening,
                    $this->dateTime
                )
            );

            return;
        }

        $videoConferenceArchive = $this->videoConferenceAdapter->archive(
            $happening->getWebinarSessionId(),
            $happening->getTitle($event->getLocaleFallback())
        );

        $this->videoConferenceAdapter->changeArchiveLayoutAuto($happening->getWebinarSessionId(), $videoConferenceArchive->id);

        $recordArchive = new RecordArchive(
            $happening,
            $videoConferenceArchive->id,
            $this->dateTime
        );

        $this->recordArchiveRepository->add($recordArchive);

        $dueDate = clone $this->dateTime->modify('+ 125minutes');
        $this->prepareReconciliationHandler->handle(
            new PrepareReconciliation(
                $happening,
                $dueDate
            )
        );

        $this->logger->info(sprintf('Webinar #%d: Start record webinar archive', $happening->getId()));

        $this->delayedEventDispatcher->dispatch(
            Events::HAPPENING_RECORDING,
            new RecordingEvent($happening)
        );
    }
}
