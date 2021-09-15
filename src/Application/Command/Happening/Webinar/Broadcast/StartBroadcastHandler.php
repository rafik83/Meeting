<?php

namespace Proximum\Vimeet\Application\Command\Happening\Webinar\Broadcast;

use DateTimeInterface;
use Proximum\Vimeet\Application\Adapter\VideoConferenceAdapterInterface;
use Proximum\Vimeet\Application\Command\Happening\Webinar\StreamCommand;
use Proximum\Vimeet\Application\Command\Happening\Webinar\StreamCommandHandler;
use Proximum\Vimeet\Application\View\Happening\Webinar\StreamDTO;
use Proximum\Vimeet\Domain\Happening\Webinar\Stream;
use Proximum\Vimeet\Domain\Model\Happening\HappeningBroadcast;
use Proximum\Vimeet\Domain\Repository\Happening\HappeningBroadcastRepositoryInterface;
use Proximum\Vimeet\Domain\Time\DaysHelper;

class StartBroadcastHandler
{
    /** @var VideoConferenceAdapterInterface */
    private $videoConferenceAdapter;

    /** @var HappeningBroadcastRepositoryInterface */
    private $broadcastRepository;

    /** @var StreamCommandHandler */
    private $streamCommandHandler;

    /** @var DateTimeInterface */
    private $dateTime;

    public function __construct(
        VideoConferenceAdapterInterface $videoConferenceAdapter,
        HappeningBroadcastRepositoryInterface $broadcastRepository,
        StreamCommandHandler $streamCommandHandler,
        DateTimeInterface $dateTime
    ) {
        $this->videoConferenceAdapter = $videoConferenceAdapter;
        $this->broadcastRepository = $broadcastRepository;
        $this->streamCommandHandler = $streamCommandHandler;
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

        // if webinar is recorded, call handler to switch layout if needed
        $this->streamCommandHandler->handle(new StreamCommand(
            $happening,
            new StreamDTO($startBroadcast->streamId, $startBroadcast->type, Stream::ACTION_START)
        ));

        return $happeningBroadcast->getHlsUrl();
    }
}
