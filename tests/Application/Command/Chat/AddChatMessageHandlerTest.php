<?php

namespace Proximum\Vimeet\Tests\Application\Command\Chat;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Command\Chat\AddChatMessage;
use Proximum\Vimeet\Application\Command\Chat\AddChatMessageHandler;
use Proximum\Vimeet\Application\Components\Chat\CheckAccessToChatMessages;
use Proximum\Vimeet\Domain\Model\ChatMessage;
use Proximum\Vimeet\Domain\Model\Meeting;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\ChatMessageRepositoryInterface;

class AddChatMessageHandlerTest extends TestCase
{
    public function testAddMessage()
    {
        $meeting = $this->prophesize(Meeting::class);
        $meeting->getObjectType()->shouldBeCalled()->willReturn('meeting');
        $meeting->getId()->shouldBeCalled()->willReturn(42);
        $user = $this->prophesize(User::class);
        $user->getFullname()->shouldBeCalled()->willReturn('Paul DUPOND');
        $sheet = $this->prophesize(Sheet::class);
        $sheet->getTitle()->shouldBeCalled()->willReturn('World Company');

        $now = new DateTimeImmutable('2020-05-12 14:12:00');

        $messageRepository = $this->prophesize(ChatMessageRepositoryInterface::class);
        $messageRepository->add(
            new ChatMessage(
                $meeting->reveal(),
                $user->reveal(),
                $now,
                'Bonjour',
                'Paul DUPOND',
                'World Company'
            )
        )->shouldBeCalled();

        $checkAccessToChatMessages = $this->prophesize(CheckAccessToChatMessages::class);
        $checkAccessToChatMessages->isSatisfiedBy($meeting->reveal(), $user->reveal())->shouldBeCalled()->willReturn(true);

        $addChatMessageHandler = new AddChatMessageHandler($messageRepository->reveal(), $checkAccessToChatMessages->reveal(), $now);
        $addChatMessageHandler->handle(new AddChatMessage($meeting->reveal(), $user->reveal(), $sheet->reveal(), 'Bonjour'));
    }
}
