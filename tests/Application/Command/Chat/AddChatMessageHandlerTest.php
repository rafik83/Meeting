<?php

namespace Proximum\Vimeet\Tests\Application\Command\Chat;

use DateTimeImmutable;
use DateTimeInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Adapter\NotificationPublisherInterface;
use Proximum\Vimeet\Application\Command\Chat\AddChatMessage;
use Proximum\Vimeet\Application\Command\Chat\AddChatMessageHandler;
use Proximum\Vimeet\Application\Components\Chat\CheckAccessToChatMessages;
use Proximum\Vimeet\Application\Exception\Chat\ChatMessageNotAllowedException;
use Proximum\Vimeet\Domain\Model\ChatMessage;
use Proximum\Vimeet\Domain\Model\ChatSession;
use Proximum\Vimeet\Domain\Model\Meeting;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\ChatMessageRepositoryInterface;

class AddChatMessageHandlerTest extends TestCase
{
    /** @var ObjectProphecy */
    private $messageRepository;

    /** @var ObjectProphecy */
    private $checkAccessToChatMessages;

    /** @var ObjectProphecy */
    private $notificationPublisher;

    /** @var AddChatMessageHandler */
    private $addChatMessageHandler;

    /** @var DateTimeInterface */
    private $now;

    protected function setUp()
    {
        $this->messageRepository = $this->prophesize(ChatMessageRepositoryInterface::class);
        $this->checkAccessToChatMessages = $this->prophesize(CheckAccessToChatMessages::class);
        $this->notificationPublisher = $this->prophesize(NotificationPublisherInterface::class);
        $this->now = new DateTimeImmutable('2020-05-12 14:12:00');

        $this->addChatMessageHandler = new AddChatMessageHandler(
            $this->messageRepository->reveal(),
            $this->checkAccessToChatMessages->reveal(),
            $this->notificationPublisher->reveal(),
            $this->now
        );
    }

    public function testAddMeetingChatMessage()
    {
        $meeting = $this->prophesize(Meeting::class);
        $meeting->getObjectType()->shouldBeCalled()->willReturn('meeting');
        $meeting->getId()->shouldBeCalled()->willReturn(42);
        $user = $this->prophesize(User::class);
        $user->getFullname()->shouldBeCalled()->willReturn('Paul DUPOND');
        $sheet = $this->prophesize(Sheet::class);
        $sheet->getTitle()->shouldBeCalled()->willReturn('World Company');

        $savedChatMesssage = $this->prophesize(ChatMessage::class);
        $savedChatMesssage->getId()->willReturn(43);
        $this->messageRepository->add(
            new ChatMessage(
                $meeting->reveal(),
                $user->reveal(),
                $this->now,
                'Bonjour',
                'Paul DUPOND',
                'World Company'
            )
        )->shouldBeCalled()->willReturn($savedChatMesssage->reveal());

        $this->checkAccessToChatMessages->isSatisfiedBy($meeting->reveal(), $user->reveal())->shouldBeCalled()->willReturn(true);

        $this->notificationPublisher->publishChatMessageNotification($meeting->reveal(), $savedChatMesssage->reveal())->shouldBeCalled();

        $this->addChatMessageHandler->handle(new AddChatMessage($meeting->reveal(), $user->reveal(), $sheet->reveal(), 'Bonjour'));
    }

    public function testAddPrivateChatMessage()
    {
        $user = $this->prophesize(User::class);
        $user->getFullname()->shouldBeCalled()->willReturn('Paul DUPOND');
        $otherUser = $this->prophesize(User::class);
        $chatSession = $this->prophesize(ChatSession::class);
        $chatSession->getObjectType()->shouldBeCalled()->willReturn('private_chat');
        $chatSession->getId()->shouldBeCalled()->willReturn(42);
        $chatSession->getOtherUser($user->reveal())->shouldBeCalled()->willReturn($otherUser);
        $chatSession->incrementUnreadMessages($otherUser->reveal())->shouldBeCalled();
        $sheet = $this->prophesize(Sheet::class);
        $sheet->getTitle()->shouldBeCalled()->willReturn('World Company');

        $savedChatMesssage = $this->prophesize(ChatMessage::class);
        $savedChatMesssage->getId()->willReturn(43);
        $this->messageRepository->add(
            new ChatMessage(
                $chatSession->reveal(),
                $user->reveal(),
                $this->now,
                'Bonjour',
                'Paul DUPOND',
                'World Company'
            )
        )->shouldBeCalled()->willReturn($savedChatMesssage->reveal());

        $this->checkAccessToChatMessages->isSatisfiedBy($chatSession->reveal(), $user->reveal())->shouldBeCalled()->willReturn(true);

        $this->notificationPublisher->publishChatMessageNotification($chatSession->reveal(), $savedChatMesssage->reveal())->shouldBeCalled();

        $this->addChatMessageHandler->handle(new AddChatMessage($chatSession->reveal(), $user->reveal(), $sheet->reveal(), 'Bonjour'));
    }

    public function testAddMessageNotAllowed()
    {
        $this->expectException(ChatMessageNotAllowedException::class);
        $chatSession = $this->prophesize(ChatSession::class);
        $user = $this->prophesize(User::class);
        $sheet = $this->prophesize(Sheet::class);

        $this->messageRepository->add(Argument::any())->shouldNotBeCalled();

        $this->checkAccessToChatMessages->isSatisfiedBy($chatSession->reveal(), $user->reveal())->shouldBeCalled()->willReturn(false);

        $this->notificationPublisher->publishChatMessageNotification(Argument::any(), Argument::any())->shouldNotBeCalled();

        $this->addChatMessageHandler->handle(new AddChatMessage($chatSession->reveal(), $user->reveal(), $sheet->reveal(), 'Bonjour'));
    }
}
