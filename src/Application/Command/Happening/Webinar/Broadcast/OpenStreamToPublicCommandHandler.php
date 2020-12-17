<?php

namespace Proximum\Vimeet\Application\Command\Happening\Webinar\Broadcast;

use Proximum\Vimeet\Application\Adapter\NotificationPublisherInterface;
use Proximum\Vimeet\Domain\Happening\Webinar\Stream;
use Proximum\Vimeet\Domain\Repository\Happening\HappeningBroadcastRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\HappeningRepositoryInterface;
use Proximum\Vimeet\Infrastructure\Adapter\Mercure\AbstractNotification;

class OpenStreamToPublicCommandHandler
{
    /** @var NotificationPublisherInterface */
    private $notificationPublisher;

    /** @var HappeningBroadcastRepositoryInterface */
    private $happeningBroadcastRepository;
    /**
     * @var HappeningRepositoryInterface
     */
    private $happeningRepository;
    /**
     * @var StartBroadcastHandler
     */
    private $startBroadcastHandler;

    public function __construct(
        NotificationPublisherInterface $notificationPublisher,
        HappeningBroadcastRepositoryInterface $happeningBroadcastRepository,
        HappeningRepositoryInterface $happeningRepository,
        StartBroadcastHandler $startBroadcastHandler
    ) {
        $this->notificationPublisher = $notificationPublisher;
        $this->happeningBroadcastRepository = $happeningBroadcastRepository;
        $this->happeningRepository = $happeningRepository;
        $this->startBroadcastHandler = $startBroadcastHandler;
    }

    public function handle(OpenStreamToPublicCommand $command): void
    {
        $happening = $command->happening;

        $happening->openStreamToPublic();

        $this->happeningRepository->set($happening);

        if ($happening->allowWebinarOnHLS()) {
            $type = $command->mediaSharingType ?: Stream::TYPE_VIDEO;
            $streamId = $command->mediaSharingStream ?: '';
            $this->startBroadcastHandler->handle(new StartBroadcast($happening, $type, $streamId));

            $happeningBroadcast = $this->happeningBroadcastRepository->getByHappening($happening);

            if (null === $happeningBroadcast) {
                throw new HappeningBroadcastForHappeningNotFoundException($happening);
            }

            $sessionReference = $happeningBroadcast->getHlsUrl();
        } else {
            $sessionReference = $happening->getWebinarSessionId();
        }

        $this->notificationPublisher->publishHappeningNotification(
            $happening,
            AbstractNotification::TYPE_STREAM,
            [
                'action' => 'stream_started',
                'sessionReference' => $sessionReference,
            ]
        );
    }
}
