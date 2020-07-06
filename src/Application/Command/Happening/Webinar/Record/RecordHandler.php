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

    public function __construct(
        VideoConferenceAdapterInterface $videoConferenceAdapter,
        RecordArchiveRepositoryInterface $recordArchiveRepository,
        \DateTimeInterface $dateTime
    ) {
        $this->videoConferenceAdapter = $videoConferenceAdapter;
        $this->recordArchiveRepository = $recordArchiveRepository;
        $this->dateTime = $dateTime;
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
    }
}
