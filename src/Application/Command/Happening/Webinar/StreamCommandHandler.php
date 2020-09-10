<?php

namespace Proximum\Vimeet\Application\Command\Happening\Webinar;

use Proximum\Vimeet\Application\Adapter\VideoConferenceAdapterInterface;
use Proximum\Vimeet\Domain\Happening\Webinar\Stream as WebinarStream;
use Proximum\Vimeet\Domain\Repository\Happening\Webinar\RecordArchiveRepositoryInterface;

class StreamCommandHandler
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

    public function handle(StreamCommand $streamCommand): void
    {
        $happening = $streamCommand->happening;

        if (!$happening->isWebinarRecorded() || null === $happening->getWebinarSessionId()) {
            return;
        }

        $startedRecordArchive = $this->recordArchiveRepository->getStartedRecordArchiveForHappening($happening);

        if (null === $startedRecordArchive) {
            return;
        }

        if ($streamCommand->stream->type !== WebinarStream::TYPE_VIDEO) {
            // On screen or custom shared, we switch the layout to vertical presentation and focus this stream.
            if ($streamCommand->stream->action === WebinarStream::ACTION_START) {
                $this->videoConferenceAdapter->changeArchiveToVertical($startedRecordArchive->getArchiveId());

                $this->videoConferenceAdapter->changeStreamClassList(
                    $happening->getWebinarSessionId(),
                    $streamCommand->stream->streamId,
                    'focus'
                );

                return;
            }

            // On stop of screenshare, we rollback to best fit.
            $this->videoConferenceAdapter->changeArchiveToBestFit($startedRecordArchive->getArchiveId());
        }
    }
}
