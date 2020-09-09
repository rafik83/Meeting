<?php

namespace Proximum\Vimeet\Application\Command\Happening\Webinar\Record;

use OpenTok\Archive;
use Proximum\Vimeet\Application\Adapter\VideoConferenceAdapterInterface;
use Proximum\Vimeet\Domain\Happening\Webinar\RecordStatus;
use Proximum\Vimeet\Domain\Model\Happening\Webinar\RecordArchive;
use Proximum\Vimeet\Domain\Repository\Happening\Webinar\RecordArchiveRepositoryInterface;

class StopRecordHandler
{
    /** @var VideoConferenceAdapterInterface */
    private $videoConferenceAdapter;

    /** @var RecordArchiveRepositoryInterface */
    private $recordArchiveRepository;

    /** @var \DateTimeInterface */
    private $dateTime;

    public function __construct(
        VideoConferenceAdapterInterface $videoConferenceAdapter,
        RecordArchiveRepositoryInterface $recordArchiveRepository,
        \DateTimeInterface $dateTime
    ) {
        $this->videoConferenceAdapter = $videoConferenceAdapter;
        $this->recordArchiveRepository = $recordArchiveRepository;
        $this->dateTime = $dateTime;
    }

    public function handle(StopRecord $stopRecord): void
    {
        $happening = $stopRecord->happening;

        $recordArchives = $this->recordArchiveRepository->getRecordArchivesForHappening($happening);
        $recordArchivesIndexedByArchiveId = $this->indexRecordArchivesByArchiveId($recordArchives);
        $archives = $this->videoConferenceAdapter->listArchives($happening->getWebinarSessionId());

        /** @var Archive $archive */
        foreach ($archives->getItems() as $archive) {
            if (in_array($archive->status, RecordStatus::IS_RECORDING_STATUS, true)) {
                $this->videoConferenceAdapter->stopArchive($archive->id);
            }

            if (isset($recordArchivesIndexedByArchiveId[$archive->id])) {
                $recordArchive = $recordArchivesIndexedByArchiveId[$archive->id];

                if (!$recordArchive->isStopped()) {
                    $recordArchive->stop();
                    $this->addPathToRecordArchive($archive, $recordArchive);

                    $this->recordArchiveRepository->update($recordArchive);
                }

                continue;
            }

            // Unknown archive from our side.
            $recordArchive = new RecordArchive(
                $happening,
                $archive->id,
                $this->dateTime
            );
            $recordArchive->stop();
            $this->addPathToRecordArchive($archive, $recordArchive);

            $this->recordArchiveRepository->add($recordArchive);
        }
    }

    private function addPathToRecordArchive(Archive $archive, RecordArchive $recordArchive): void
    {
        $archiveUrl = $archive->url;

        if (!empty($archiveUrl)) {
            $recordArchive->addPathToRecordArchive($archiveUrl);
        }
    }

    /**
     * @param RecordArchive[] $recordArchives
     *
     * @return RecordArchive[]
     */
    private function indexRecordArchivesByArchiveId(array $recordArchives): array
    {
        $recordArchivesIndexedByArchiveId = [];

        foreach ($recordArchives as $recordArchive) {
            $recordArchivesIndexedByArchiveId[$recordArchive->getArchiveId()] = $recordArchive;
        }

        return $recordArchivesIndexedByArchiveId;
    }
}
