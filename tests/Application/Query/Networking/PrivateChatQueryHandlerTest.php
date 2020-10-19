<?php

namespace Proximum\Vimeet\Tests\Application\Query\Networking;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Adapter\NotificationSubscriberInterface;
use Proximum\Vimeet\Application\Exception\Chat\PrivateChatInvalidToUser;
use Proximum\Vimeet\Application\Query\Networking\PrivateChatQuery;
use Proximum\Vimeet\Application\Query\Networking\PrivateChatQueryHandler;
use Proximum\Vimeet\Domain\KeyDates\Checker\CallVisioPrivateChatAccessChecker;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\ChatSessionRepositoryInterface;

class PrivateChatQueryHandlerTest extends TestCase
{
    public function testPrivateChatInvalidToUser(): void
    {

        $notificationSubscriber = $this->prophesize(NotificationSubscriberInterface::class);
        $chatSessionRepository = $this->prophesize(ChatSessionRepositoryInterface::class);
        $callVisioPrivateChatAccessChecker = $this->prophesize(CallVisioPrivateChatAccessChecker::class);
        $privateChatQueryHandler = new PrivateChatQueryHandler($notificationSubscriber->reveal(), $chatSessionRepository->reveal(), $callVisioPrivateChatAccessChecker->reveal());
        $sheet = $this->prophesize(Sheet::class);
        $user = $this->prophesize(User::class);
        $user->getId()->shouldBeCalled()->willReturn(333);
        $privateChatQuery = new PrivateChatQuery($sheet->reveal(), $user->reveal(), $user->reveal());
        $this->expectException(PrivateChatInvalidToUser::class);
        $privateChatQueryHandler->handle($privateChatQuery);
    }
}
