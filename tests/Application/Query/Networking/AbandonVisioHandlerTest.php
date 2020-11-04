<?php


namespace Proximum\Vimeet\Tests\Application\Query\Networking;


use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Adapter\NotificationPublisherInterface;
use Proximum\Vimeet\Application\Command\Networking\AbandonVisio;
use Proximum\Vimeet\Application\Command\Networking\AbandonVisioHandler;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;

class AbandonVisioHandlerTest extends TestCase
{
    public function testAbandonVisio(): void {
        $notificationPublisher = $this->prophesize(NotificationPublisherInterface::class);
        $sheet = $this->prophesize(Sheet::class);
        $fromUser = $this->prophesize(User::class);
        $toUserId = $this->prophesize(User::class);
        $toUserId->getId()->shouldBeCalled()->willReturn(333);
        $notificationPublisher->publishRequestVisioNotification($sheet->reveal(), $fromUser->reveal(), 333, 'abandon_visio')->shouldBeCalled();
        $command = new AbandonVisio($sheet->reveal(), $fromUser->reveal(), $toUserId->reveal());
        $handler = new AbandonVisioHandler($notificationPublisher->reveal());
        $handler->handle($command);
    }
}
