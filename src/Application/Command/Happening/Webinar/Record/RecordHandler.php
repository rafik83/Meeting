<?php

namespace Proximum\Vimeet\Application\Command\Happening\Webinar\Record;

use Proximum\Vimeet\Application\Adapter\VideoConferenceAdapterInterface;
use Proximum\Vimeet\Domain\Happening\Webinar\RecordStatus;
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

    public function __construct(
        VideoConferenceAdapterInterface $videoConferenceAdapter,
        RecordArchiveRepositoryInterface $recordArchiveRepository,
        PrepareReconciliationHandler $prepareReconciliationHandler,
        \DateTimeInterface $dateTime,
        LoggerInterface $logger
    ) {
        $this->videoConferenceAdapter = $videoConferenceAdapter;
        $this->recordArchiveRepository = $recordArchiveRepository;
        $this->prepareReconciliationHandler = $prepareReconciliationHandler;
        $this->dateTime = $dateTime;
        $this->logger = $logger;
    }

    public function handle(Record $record): void
    {
        $happening = $record->happening;
        $event = $happening->getEvent();

        $existingArchives = $this->videoConferenceAdapter->listArchives($happening->getWebinarSessionId());
        $startedArchives = array_filter($existingArchives->getItems(), static function ($archiveItem) {
            return $archiveItem->status === RecordStatus::STARTED;
        });

        if (count($startedArchives) > 0) {
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
    }
}
