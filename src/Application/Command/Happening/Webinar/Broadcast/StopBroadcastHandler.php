<?php

namespace Proximum\Vimeet\Application\Command\Happening\Webinar\Broadcast;

use Proximum\Vimeet\Application\Adapter\VideoConferenceAdapterInterface;
use Proximum\Vimeet\Domain\Repository\Happening\HappeningBroadcastRepositoryInterface;

class StopBroadcastHandler
{
    /** @var VideoConferenceAdapterInterface */
    private $videoConferenceAdapter;
    /** @var HappeningBroadcastRepositoryInterface */
    private $broadcastRepository;

    public function __construct(
        VideoConferenceAdapterInterface $videoConferenceAdapter,
        HappeningBroadcastRepositoryInterface $broadcastRepository
    ) {
        $this->videoConferenceAdapter = $videoConferenceAdapter;
        $this->broadcastRepository = $broadcastRepository;
    }

    public function handle(StopBroadcast $stopBroadcast): void
    {
        $broadcast = $this->broadcastRepository->getByHappening($stopBroadcast->happening);

        if ($broadcast === null) {
            return;
        }

        $this->videoConferenceAdapter->stopBroadcast($broadcast->getBroadcastId());

        $broadcast->stop();
        $this->broadcastRepository->update($broadcast);
    }
}
