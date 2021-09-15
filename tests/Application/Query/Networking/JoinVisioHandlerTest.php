<?php


namespace Proximum\Vimeet\Tests\Application\Query\Networking;


use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Adapter\NotificationPublisherInterface;
use Proximum\Vimeet\Application\Command\Networking\JoinVisio;
use Proximum\Vimeet\Application\Command\Networking\JoinVisioHandler;
use Proximum\Vimeet\Domain\Model\ChatSession;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Infrastructure\Repository\ChatSessionRepository;

class JoinVisioHandlerTest extends TestCase
{
    public function testJoinVisio(): void {
        $notificationPublisher = $this->prophesize(NotificationPublisherInterface::class);
        $chatSessionRepository = $this->prophesize(ChatSessionRepository::class);
        $chatSession = $this->prophesize(ChatSession::class);
        $now = new \DateTimeImmutable();
        $event = $this->prophesize(Event::class);
        $sheet = $this->prophesize(Sheet::class);
        $fromUser = $this->prophesize(User::class);
        $toUser = $this->prophesize(User::class);
        $toUser->getId()->shouldBeCalled()->willReturn(333);
        $sheet->getEvent()->shouldBeCalled()->willReturn($event->reveal());
        $chatSessionRepository->findOneByEventAndUsers($event->reveal(), $fromUser->reveal(), $toUser->reveal())->willReturn($chatSession->reveal());
        $notificationPublisher->publishRequestVisioNotification($sheet->reveal(), $fromUser->reveal(), 333, 'join_visio')->shouldBeCalled();
        $chatSession->setVisioStartedAt($now)->shouldBeCalled();
        $chatSessionRepository->update()->shouldBeCalled();
        $command = new JoinVisio($sheet->reveal(), $fromUser->reveal(), $toUser->reveal());
        $handler = new JoinVisioHandler($notificationPublisher->reveal(), $chatSessionRepository->reveal(), $now);
        $handler->handle($command);
    }
}
