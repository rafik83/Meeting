<?php

namespace Proximum\Vimeet\Tests\Application\Command\Happening\Webinar\Broadcast;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Proximum\Vimeet\Application\Adapter\NotificationPublisherInterface;
use Proximum\Vimeet\Application\Adapter\NotificationSubscriptionsInterface;
use Proximum\Vimeet\Application\Command\Happening\Webinar\Broadcast\StartViewer;
use Proximum\Vimeet\Application\Command\Happening\Webinar\Broadcast\StartViewerHandler;
use Proximum\Vimeet\Domain\Model\Happening;

class StartViewerHandlerTest extends TestCase
{
    public function testHandle()
    {
        $happeningId = 1789;
        $happening = $this->prophesize(Happening::class);
        $happening->getId()->willReturn($happeningId);
        $happening->isStreamOpenToPublic()->willReturn(false);
        $happening->allowWebinarOnHLS()->willReturn(true);

        $subscriptionsCount = 42;
        $notificationSubscriptions = $this->prophesize(NotificationSubscriptionsInterface::class);
        $notificationSubscriptions->getStreamSubscriptionsCount($happeningId)->shouldBeCalled()->willReturn($subscriptionsCount);

        $notificationPublisher = $this->prophesize(NotificationPublisherInterface::class);
        $notificationPublisher->publishHappeningNotification($happening->reveal(), 'stream', [
            'action' => 'viewer_connected',
            'connectedUsersCount' => $subscriptionsCount,
        ])->shouldBeCalled();

        $startViewerHandler = new StartViewerHandler(
            $notificationSubscriptions->reveal(),
            $notificationPublisher->reveal()
        );
        $startViewerHandler->handle(new StartViewer($happening->reveal()));
    }

    public function testNoPublicationForWebRTCAndOpenStream(): void
    {
        $happening = $this->prophesize(Happening::class);
        $happening->isStreamOpenToPublic()->willReturn(true);
        $happening->allowWebinarOnHLS()->willReturn(false);

        $notificationSubscriptions = $this->prophesize(NotificationSubscriptionsInterface::class);

        $notificationPublisher = $this->prophesize(NotificationPublisherInterface::class);
        $notificationPublisher->publishHappeningNotification(Argument::any(), Argument::any(), Argument::any())->shouldNotBeCalled();

        $startViewerHandler = new StartViewerHandler(
            $notificationSubscriptions->reveal(),
            $notificationPublisher->reveal()
        );
        $startViewerHandler->handle(new StartViewer($happening->reveal()));
    }
}
