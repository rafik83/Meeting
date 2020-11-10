<?php

namespace Proximum\Vimeet\Tests\Application\Command\Happening\Webinar\Broadcast;

use PHPUnit\Framework\TestCase;
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
}
