<?php

namespace Proximum\Vimeet\Application\Command\Happening\Webinar\Record;

use Proximum\Vimeet\Application\Adapter\VideoConferenceAdapterInterface;
use Proximum\Vimeet\Domain\Model\Happening\Webinar\RecordArchive;
use Proximum\Vimeet\Domain\Repository\Happening\Webinar\RecordArchiveRepositoryInterface;

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

    public function __construct(
        VideoConferenceAdapterInterface $videoConferenceAdapter,
        RecordArchiveRepositoryInterface $recordArchiveRepository,
        PrepareReconciliationHandler $prepareReconciliationHandler,
        \DateTimeInterface $dateTime
    ) {
        $this->videoConferenceAdapter = $videoConferenceAdapter;
        $this->recordArchiveRepository = $recordArchiveRepository;
        $this->dateTime = $dateTime;
        $this->prepareReconciliationHandler = $prepareReconciliationHandler;
    }

    public function handle(Record $record): void
    {
        $happening = $record->happening;
        $event = $happening->getEvent();

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

        $dueDate = clone $this->dateTime;
        $dueDate->modify('+ 125minutes');
        $this->prepareReconciliationHandler->handle(
            new PrepareReconciliation(
                $happening,
                $dueDate
            )
        );
    }
}
