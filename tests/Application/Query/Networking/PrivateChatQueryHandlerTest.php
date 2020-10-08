<?php

namespace Proximum\Vimeet\Tests\Application\Query\Networking;

use Proximum\Vimeet\Application\Adapter\NotificationSubscriberInterface;
use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Exception\Chat\PrivateChatInvalidToUser;
use Proximum\Vimeet\Application\Query\Networking\PrivateChatQuery;
use Proximum\Vimeet\Application\Query\Networking\PrivateChatQueryHandler;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\ChatSessionRepositoryInterface;

class PrivateChatQueryHandlerTest extends TestCase
{
    public function testPrivateChatInvalidToUser(): void {

        $notificationSubscriber = $this->prophesize(NotificationSubscriberInterface::class);
        $chatSessionRepository = $this->prophesize(ChatSessionRepositoryInterface::class);
        $privateChatQueryHandler = new PrivateChatQueryHandler($notificationSubscriber->reveal(), $chatSessionRepository->reveal());
        $event = $this->prophesize(Event::class);
        $user = $this->prophesize(User::class);
        $user->getId()->shouldBeCalled()->willReturn(333);
        $privateChatQuery = new PrivateChatQuery($event->reveal(), $user->reveal(), $user->reveal());
        $this->expectException(PrivateChatInvalidToUser::class);
        $privateChatQueryHandler->handle($privateChatQuery);
    }
}
