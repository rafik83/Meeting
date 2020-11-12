<?php

namespace Proximum\Vimeet\Application\Command\Happening\Webinar\Broadcast;

use OpenTok\Exception\BroadcastDomainException;
use Proximum\Vimeet\Application\Adapter\VideoConferenceAdapterInterface;
use Proximum\Vimeet\Domain\Happening\Webinar\Stream;
use Proximum\Vimeet\Domain\Model\Happening;
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
        $happening = $stopBroadcast->happening;
        $broadcast = $this->broadcastRepository->getByHappening($happening);

        if ($broadcast === null) {
            return;
        }

        if ($this->videoConferenceAdapter->getSessionStreamCount($happening->getWebinarSessionId()) > 0) {
            // broadcast is active, we reset layout if stopped stream is not video (screen or video share)
            // and don't stop broadcast since it must be kept for remaining speaker(s)
            if ($stopBroadcast->type !== Stream::TYPE_VIDEO) {
                $this->videoConferenceAdapter->resetBroadcastFocus($broadcast->getBroadcastId());
            }

            return;
        }

        try {
            $this->videoConferenceAdapter->stopBroadcast($broadcast->getBroadcastId());
        } catch (BroadcastDomainException $exception) {
            $this->handleAlreadyStoppedBroadcast($happening);
        }

        $this->broadcastRepository->deleteForHappening($happening);
    }

    private function handleAlreadyStoppedBroadcast(Happening $happening): void
    {
        $broadcasts = $this->videoConferenceAdapter->getBroadcastsForSession($happening->getWebinarSessionId());

        foreach ($broadcasts as $broadcast) {
            try {
                $this->videoConferenceAdapter->stopBroadcast($broadcast->getBroadcastId());
            } catch (BroadcastDomainException $exception) {
                // Even if stopped, broadcast can still appear in the api
                continue;
            }
        }
    }
}
