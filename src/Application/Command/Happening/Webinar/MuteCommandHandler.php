<?php


namespace Proximum\Vimeet\Application\Command\Happening\Webinar;

use Proximum\Vimeet\Infrastructure\Adapter\Mercure\AbstractNotification;
use Proximum\Vimeet\Infrastructure\Adapter\Mercure\NotificationPublisher;

class MuteCommandHandler
{
    private NotificationPublisher $notificationPublisher;

    public function __construct(
        NotificationPublisher $notificationPublisher
    ) {
        $this->notificationPublisher = $notificationPublisher;
    }

    public function handle(MuteCommand $muteCommand): void
    {
        $happening = $muteCommand->happening;
        $userId = $muteCommand->userId;
        $data = [
            'userId'=> $userId,
            'action'=> 'mute_stream',
        ];
        $this->notificationPublisher->publishHappeningNotification($happening,AbstractNotification::TYPE_STREAM, $data);
    }

}
