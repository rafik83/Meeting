<?php

namespace Proximum\Vimeet\Tests\Application\Command\Chat;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Adapter\EntityManagerAdapterInterface;
use Proximum\Vimeet\Application\Command\Chat\ResetSessionUnreadMessages;
use Proximum\Vimeet\Application\Command\Chat\ResetSessionUnreadMessagesHandler;
use Proximum\Vimeet\Domain\Model\ChatSession;
use Proximum\Vimeet\Domain\Model\User;

class ResetSessionUnreadMessagesHandlerTest extends TestCase
{
    public function testResetSessionUnreadMessages()
    {
        $entityManagerAdapter = $this->prophesize(EntityManagerAdapterInterface::class);

        $user = $this->prophesize(User::class);
        $chatSession = $this->prophesize(ChatSession::class);
        $chatSession->resetUnreadMessages($user->reveal())->shouldBeCalled();
        $entityManagerAdapter->flush($chatSession->reveal())->shouldBeCalled();

        $handler = new ResetSessionUnreadMessagesHandler($entityManagerAdapter->reveal());
        $handler->handle(new ResetSessionUnreadMessages($chatSession->reveal(), $user->reveal()));
    }
}
