<?php

namespace Proximum\Vimeet\Application\Command\Happening\Webinar\Record;

use OpenTok\Archive;
use Proximum\Vimeet\Application\Adapter\VideoConferenceAdapterInterface;
use Proximum\Vimeet\Domain\Happening\Webinar\RecordStatus;
use Proximum\Vimeet\Domain\Model\Happening\Webinar\RecordArchive;
use Proximum\Vimeet\Domain\Repository\Happening\Webinar\RecordArchiveRepositoryInterface;

class ReconciliateHandler
{
    /** @var VideoConferenceAdapterInterface */
    private $videoConferenceAdapter;

    /** @var RecordArchiveRepositoryInterface */
    private $recordArchiveRepository;

    /** @var \DateTimeInterface */
    private $dateTime;

    public function __construct(
        RecordArchiveRepositoryInterface $recordArchiveRepository,
        VideoConferenceAdapterInterface $videoConferenceAdapter,
        \DateTimeInterface $dateTime
    ) {
        $this->videoConferenceAdapter = $videoConferenceAdapter;
        $this->recordArchiveRepository = $recordArchiveRepository;
        $this->dateTime = $dateTime;
    }

    public function handle(Reconciliate $reconciliate): void
    {
        $happening = $reconciliate->happening;

        if (null === $happening->getWebinarSessionId()) {
            return;
        }

        $listArchive = $this->videoConferenceAdapter->listArchives($happening->getWebinarSessionId());
        $recordArchives = $this->recordArchiveRepository->getRecordArchivesForHappening($happening);
        $recordArchivesIndexedByArchiveId = $this->indexRecordArchivesByArchiveId($recordArchives);

dump($listArchive->getItems());
        /** @var Archive $archive */
        foreach ($listArchive->getItems() as $archive) {
            if (isset($recordArchivesIndexedByArchiveId[$archive->id])) {
                $recordArchive = $recordArchivesIndexedByArchiveId[$archive->id];

                if (!in_array($archive->status, RecordStatus::IS_RECORDING_STATUS, true)) {
                    $recordArchive->stop();
                }

                $this->addPathToRecordArchive($archive, $recordArchive);
                $this->recordArchiveRepository->update($recordArchive);

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
