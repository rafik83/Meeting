<?php

namespace Proximum\Vimeet\Tests\Domain\Chat;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Domain\Chat\CanDeleteChatMessage;
use Proximum\Vimeet\Domain\Model\ChatSession;
use Proximum\Vimeet\Domain\Model\Happening;
use Proximum\Vimeet\Domain\Model\User;

class CanDeleteChatMessageTest extends TestCase
{
    public function testIsSatisfiedForHappening()
    {
        $happening = $this->prophesize(Happening::class);
        $user = $this->prophesize(User::class);
        $happening->hasSpeaker($user->reveal())->shouldBeCalled()->willReturn(true);

        $canDeleteChatMessage = new CanDeleteChatMessage();
        $this->assertTrue($canDeleteChatMessage->isSatisfiedBy($happening->reveal(), $user->reveal()));
    }

    public function testIsNotSatisfiedForChatSession()
    {
        $chatSession = $this->prophesize(ChatSession::class);
        $user = $this->prophesize(User::class);

        $canDeleteChatMessage = new CanDeleteChatMessage();
        $this->assertFalse($canDeleteChatMessage->isSatisfiedBy($chatSession->reveal(), $user->reveal()));
    }

    public function testIsNotSatisfiedIfUserNotSpeaker()
    {
        $happening = $this->prophesize(Happening::class);
        $user = $this->prophesize(User::class);
        $happening->hasSpeaker($user->reveal())->shouldBeCalled()->willReturn(false);

        $canDeleteChatMessage = new CanDeleteChatMessage();
        $this->assertFalse($canDeleteChatMessage->isSatisfiedBy($happening->reveal(), $user->reveal()));
    }
}
