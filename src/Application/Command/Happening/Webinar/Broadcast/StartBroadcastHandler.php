<?php

namespace Proximum\Vimeet\Application\Command\Happening\Webinar\Broadcast;

use DateTimeInterface;
use Proximum\Vimeet\Application\Adapter\VideoConferenceAdapterInterface;
use Proximum\Vimeet\Domain\Model\Happening\HappeningBroadcast;
use Proximum\Vimeet\Domain\Repository\Happening\HappeningBroadcastRepositoryInterface;
use Proximum\Vimeet\Domain\Time\DaysHelper;

class StartBroadcastHandler
{
    /** @var VideoConferenceAdapterInterface */
    private $videoConferenceAdapter;

    /** @var DateTimeInterface */
    private $dateTime;

    /** @var HappeningBroadcastRepositoryInterface */
    private $broadcastRepository;

    public function __construct(
        VideoConferenceAdapterInterface $videoConferenceAdapter,
        HappeningBroadcastRepositoryInterface $broadcastRepository,
        DateTimeInterface $dateTime
    ) {
        $this->videoConferenceAdapter = $videoConferenceAdapter;
        $this->broadcastRepository = $broadcastRepository;
        $this->dateTime = $dateTime;
    }

    public function handle(StartBroadcast $startBroadcast): void
    {
        $happening = $startBroadcast->happening;
        $end = DaysHelper::cloneDateTime($happening->getEnd());
        $end = $end->modify('+30 minutes');

        $maxDuration = 36000;
        $duration = max((int) $end->getTimestamp() - $this->dateTime->getTimestamp(), 0);

        if (0 === $duration) {
            // throw exception, can not be 0
            $duration = 3600;
        }

        // A broadcast can't last more than 10hours.
        if ($duration > $maxDuration) {
            $duration = $maxDuration;
            $end = DaysHelper::cloneDateTime($this->dateTime);
            $end = $end->modify("+$maxDuration seconds");
        }

        $broadcast = $this->videoConferenceAdapter->startBroadcast(
            $happening->getWebinarSessionId(),
            $duration
        );

        $happeningBroadcast = new HappeningBroadcast(
            $happening,
            $broadcast->getBroadcastId(),
            false,
            $this->dateTime,
            $end,
            $broadcast->getHlsUrl()
        );

        $this->broadcastRepository->deleteForHappening($happening);
        $this->broadcastRepository->add($happeningBroadcast);
    }
}
