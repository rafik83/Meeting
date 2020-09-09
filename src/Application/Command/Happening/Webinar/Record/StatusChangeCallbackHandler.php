<?php

namespace Proximum\Vimeet\Application\Command\Happening\Webinar\Record;

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

    /** @var \DateTimeInterface */
    private $dateTime;

    public function __construct(
        RecordArchiveRepositoryInterface $recordArchiveRepository,
        HappeningRepositoryInterface $happeningRepository,
        \DateTimeInterface $dateTime
    ) {
        $this->recordArchiveRepository = $recordArchiveRepository;
        $this->happeningRepository = $happeningRepository;
        $this->dateTime = $dateTime;
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
                $this->addPathToRecordArchive($recordArchive, $statusChangeCallback->url);
            }

            $this->recordArchiveRepository->add($recordArchive);

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
        $this->addPathToRecordArchive($recordArchive, $statusChangeCallback->url);
        $this->recordArchiveRepository->update($recordArchive);
    }

    private function addPathToRecordArchive(RecordArchive $recordArchive, ?string $url = null): void
    {
        if (!empty($url)) {
            $recordArchive->addPathToRecordArchive($url);
        }
    }
}
