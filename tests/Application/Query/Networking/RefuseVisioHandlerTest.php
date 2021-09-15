<?php


namespace Proximum\Vimeet\Tests\Application\Query\Networking;


use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Adapter\NotificationPublisherInterface;
use Proximum\Vimeet\Application\Command\Networking\RefuseVisio;
use Proximum\Vimeet\Application\Command\Networking\RefuseVisioHandler;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;

class RefuseVisioHandlerTest  extends TestCase
{
    public function testRefuseVisio(): void {
        $notificationPublisher = $this->prophesize(NotificationPublisherInterface::class);
        $sheet = $this->prophesize(Sheet::class);
        $fromUser = $this->prophesize(User::class);
        $toUserId = $this->prophesize(User::class);
        $toUserId->getId()->shouldBeCalled()->willReturn(333);
        $notificationPublisher->publishRequestVisioNotification($sheet->reveal(), $fromUser->reveal(), 333, 'refuse_visio')->shouldBeCalled();
        $command = new RefuseVisio($sheet->reveal(), $fromUser->reveal(), $toUserId->reveal());
        $handler = new RefuseVisioHandler($notificationPublisher->reveal());
        $handler->handle($command);
    }
}
