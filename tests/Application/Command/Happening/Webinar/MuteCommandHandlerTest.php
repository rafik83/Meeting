<?php


namespace Proximum\Vimeet\Tests\Application\Command\Happening\Webinar;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Command\Happening\Webinar\MuteCommand;
use Proximum\Vimeet\Application\Command\Happening\Webinar\MuteCommandHandler;
use Proximum\Vimeet\Domain\Model\Happening;
use Proximum\Vimeet\Infrastructure\Adapter\Mercure\NotificationPublisher;

class MuteCommandHandlerTest extends TestCase
{

    public function testHandle():void
    {
        $notificationPublisher = $this->prophesize(NotificationPublisher::class);
        $happening = $this->prophesize(Happening::class);
        $userId = 123;
        $notificationPublisher
            ->publishHappeningNotification($happening->reveal(), 'stream', ['userId' => $userId, 'action' => 'mute_stream'])
            ->shouldBeCalled();
        $muteCommandHandler = new MuteCommandHandler($notificationPublisher->reveal());

        $muteCommandHandler->handle(new MuteCommand($happening->reveal(),$userId));
    }
}
