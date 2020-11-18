<?php

namespace Proximum\Vimeet\Application\Command\Happening\Webinar\Broadcast;

use Proximum\Vimeet\Application\Adapter\NotificationPublisherInterface;
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

    public function __construct(
        NotificationPublisherInterface $notificationPublisher,
        HappeningBroadcastRepositoryInterface $happeningBroadcastRepository,
        HappeningRepositoryInterface $happeningRepository
    ) {
        $this->notificationPublisher = $notificationPublisher;
        $this->happeningBroadcastRepository = $happeningBroadcastRepository;
        $this->happeningRepository = $happeningRepository;
    }

    public function handle(OpenStreamToPublicCommand $command): void
    {
        $happening = $command->happening;

        $happeningBroadcast = $this->happeningBroadcastRepository->getByHappening($happening);

        if (null === $happeningBroadcast) {
            throw new HappeningBroadcastForHappeningNotFoundException($happening);
        }

        $happening->openStreamToPublic();

        $this->happeningRepository->set($happening);

        $this->notificationPublisher->publishHappeningNotification(
            $happening,
            AbstractNotification::TYPE_STREAM,
            [
                'action' => 'stream_started',
                'hlsUrl' => $happeningBroadcast->getHlsUrl(),
            ]
        );
    }
}
