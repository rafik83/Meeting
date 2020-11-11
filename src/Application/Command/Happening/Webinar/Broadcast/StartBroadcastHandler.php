<?php

namespace Proximum\Vimeet\Application\Command\Happening\Webinar\Broadcast;

use DateTimeInterface;
use Proximum\Vimeet\Application\Adapter\NotificationPublisherInterface;
use Proximum\Vimeet\Application\Adapter\VideoConferenceAdapterInterface;
use Proximum\Vimeet\Domain\Happening\Webinar\Stream;
use Proximum\Vimeet\Domain\Model\Happening\HappeningBroadcast;
use Proximum\Vimeet\Domain\Repository\Happening\HappeningBroadcastRepositoryInterface;
use Proximum\Vimeet\Domain\Time\DaysHelper;
use Proximum\Vimeet\Infrastructure\Adapter\Mercure\AbstractNotification;

class StartBroadcastHandler
{
    /** @var VideoConferenceAdapterInterface */
    private $videoConferenceAdapter;

    /** @var DateTimeInterface */
    private $dateTime;

    /** @var HappeningBroadcastRepositoryInterface */
    private $broadcastRepository;

    /** @var NotificationPublisherInterface */
    private $notificationPublisher;

    public function __construct(
        VideoConferenceAdapterInterface $videoConferenceAdapter,
        HappeningBroadcastRepositoryInterface $broadcastRepository,
        NotificationPublisherInterface $notificationPublisher,
        DateTimeInterface $dateTime
    ) {
        $this->videoConferenceAdapter = $videoConferenceAdapter;
        $this->broadcastRepository = $broadcastRepository;
        $this->notificationPublisher = $notificationPublisher;
        $this->dateTime = $dateTime;
    }

    public function handle(StartBroadcast $startBroadcast): ?string
    {
        $happening = $startBroadcast->happening;

        $sessionId = $happening->getWebinarSessionId();

        // manage broadcast
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

        $broadcast = $this->videoConferenceAdapter->getBroadcastForSession($sessionId);

        if (null === $broadcast) {
            $broadcast = $this->videoConferenceAdapter->startBroadcast(
                $sessionId,
                $duration
            );
        }

        // adapt layout if needed
        if ($startBroadcast->type !== Stream::TYPE_VIDEO) {
            $this->videoConferenceAdapter->changeBroadcastFocus($broadcast, $startBroadcast->streamId);
        }

        // store broadcast info
        $happeningBroadcast = $this->broadcastRepository->getByHappening($happening);
        if (null === $happeningBroadcast || $happeningBroadcast->getHlsUrl() !== $broadcast->getHlsUrl()) {
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

        $this->notificationPublisher->publishHappeningNotification($happening, AbstractNotification::TYPE_STREAM, [
            'action' => 'stream_started',
            'hlsUrl' => $happeningBroadcast->getHlsUrl(),
        ]);

        return $happeningBroadcast->getHlsUrl();
    }
}
