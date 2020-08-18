<?php

namespace Proximum\Vimeet\Application\Command\Happening\Webinar\Record;

use DateTimeInterface;
use Proximum\Vimeet\Domain\Happening\Webinar\RecordStatus;
use Proximum\Vimeet\Domain\Model\Happening\Webinar\RecordArchive;
use Proximum\Vimeet\Domain\Repository\Happening\Webinar\RecordArchiveRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\HappeningRepositoryInterface;

class StatusChangeCallbackHandler
{
    /** @var RecordArchiveRepositoryInterface */
    private $recordArchiveRepository;

    /** @var HappeningRepositoryInterface */
    private $happeningRepository;

    /** @var DateTimeInterface */
    private $dateTime;

    /** @var PrepareZipRecordArchiveHandler */
    private $prepareZipRecordArchiveHandler;

    public function __construct(
        RecordArchiveRepositoryInterface $recordArchiveRepository,
        HappeningRepositoryInterface $happeningRepository,
        PrepareZipRecordArchiveHandler $prepareZipRecordArchiveHandler,
        DateTimeInterface $dateTime
    ) {
        $this->recordArchiveRepository = $recordArchiveRepository;
        $this->happeningRepository = $happeningRepository;
        $this->dateTime = $dateTime;
        $this->prepareZipRecordArchiveHandler = $prepareZipRecordArchiveHandler;
    }

    public function handle(StatusChangeCallback $statusChangeCallback): void
    {
        $status = $statusChangeCallback->status;
        $recordArchive = $this->recordArchiveRepository->getByArchiveId($statusChangeCallback->archiveId);

        if (null === $recordArchive) {
            $happening = $this->happeningRepository->findWebinarBySessionId($statusChangeCallback->sessionId);

            if (null === $happening) {
                return;
            }

            $recordArchive = new RecordArchive(
                $happening,
                $statusChangeCallback->archiveId,
                $this->dateTime
            );

            if (!in_array($status, RecordStatus::IS_RECORDING_STATUS, true)) {
                $recordArchive->stop();
            }

            $this->recordArchiveRepository->add($recordArchive);

            if ($happening->hasWebinarRecordZipFileUrl()) {
                $this->prepareZipRecordArchiveHandler->handle(
                    new PrepareZipRecordArchive(
                        $happening,
                        true
                    )
                );
            }

            return;
        }

        if ($recordArchive->getStatus() === $status) {
            return;
        }

        if (in_array($status, RecordStatus::IS_RECORDING_STATUS, true)) {
            if ($recordArchive->isStopped()) {
                // Should not happen !
                $recordArchive->unstop();
                $this->recordArchiveRepository->update($recordArchive);
            }

            return;
        }

        $recordArchive->stop();
        $this->recordArchiveRepository->update($recordArchive);

        if ($recordArchive->getHappening()->hasWebinarRecordZipFileUrl()) {
            $this->prepareZipRecordArchiveHandler->handle(
                new PrepareZipRecordArchive(
                    $recordArchive->getHappening(),
                    true
                )
            );
        }
    }
}
