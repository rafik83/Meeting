<?php

namespace Proximum\Vimeet\Application\Command\Happening\Webinar\Record;

use Proximum\Vimeet\Application\Adapter\VideoConferenceAdapterInterface;
use Proximum\Vimeet\Domain\Repository\Happening\Webinar\RecordArchiveRepositoryInterface;

class StopRecordHandler
{
    /** @var VideoConferenceAdapterInterface */
    private $videoConferenceAdapter;

    /** @var RecordArchiveRepositoryInterface */
    private $recordArchiveRepository;

    public function __construct(
        VideoConferenceAdapterInterface $videoConferenceAdapter,
        RecordArchiveRepositoryInterface $recordArchiveRepository
    ) {
        $this->videoConferenceAdapter = $videoConferenceAdapter;
        $this->recordArchiveRepository = $recordArchiveRepository;
    }

    public function handle(StopRecord $stopRecord): void
    {
        $happening = $stopRecord->happening;

        $recordArchives = $this->recordArchiveRepository->getStartedRecordArchiveForHappening($happening);

        foreach ($recordArchives as $recordArchive) {
            $this->videoConferenceAdapter->stopArchive($recordArchive->getArchiveId());
            $recordArchive->stop();

            $this->recordArchiveRepository->update($recordArchive);
        }
    }
}
