<?php

namespace Proximum\Vimeet\Tests\Application\Command\Chat;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Adapter\NotificationPublisherInterface;
use Proximum\Vimeet\Application\Command\Chat\DeleteChatMessage;
use Proximum\Vimeet\Application\Command\Chat\DeleteChatMessageHandler;
use Proximum\Vimeet\Application\Exception\Chat\ChatMessageNotAllowedException;
use Proximum\Vimeet\Application\Exception\Chat\ChatMessageNotFoundException;
use Proximum\Vimeet\Application\Exception\Chat\DeleteChatMessageNotAllowedException;
use Proximum\Vimeet\Application\Query\Chat\GuessChatMessageLinkableObject;
use Proximum\Vimeet\Application\Query\Chat\GuessChatMessageLinkableObjectHandler;
use Proximum\Vimeet\Domain\Chat\CanDeleteChatMessage;
use Proximum\Vimeet\Domain\Model\ChatMessage;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Happening;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\ChatMessageRepositoryInterface;

class DeleteChatMessageHandlerTest extends TestCase
{
    /** @var ObjectProphecy */
    private $messageRepository;

    /** @var ObjectProphecy */
    private $guessChatMessageLinkableObjectHandler;

    /** @var ObjectProphecy */
    private $canDeleteChatMessage;

    /** @var ObjectProphecy */
    private $notificationPublisher;

    /** @var DeleteChatMessageHandler */
    private $deleteChatMessageHandler;

    protected function setUp()
    {
        $this->messageRepository = $this->prophesize(ChatMessageRepositoryInterface::class);
        $this->guessChatMessageLinkableObjectHandler = $this->prophesize(GuessChatMessageLinkableObjectHandler::class);
        $this->canDeleteChatMessage = $this->prophesize(CanDeleteChatMessage::class);
        $this->notificationPublisher = $this->prophesize(NotificationPublisherInterface::class);

        $this->deleteChatMessageHandler = new DeleteChatMessageHandler(
            $this->messageRepository->reveal(),
            $this->guessChatMessageLinkableObjectHandler->reveal(),
            $this->canDeleteChatMessage->reveal(),
            $this->notificationPublisher->reveal()
        );
    }

    public function testDeleteHappeningChatMessage()
    {
        $event = $this->prophesize(Event::class);
        $event->getId()->willReturn(123);
        $happening = $this->prophesize(Happening::class);
        $happening->getEvent()->willReturn($event->reveal());
        $user = $this->prophesize(User::class);

        $chatMessage = $this->prophesize(ChatMessage::class);
        $chatMessage->getObjectType()->willReturn(ChatMessage::TYPE_HAPPENING);
        $chatMessage->getObjectId()->willReturn(2002);
        $this->messageRepository->findById(4242)->shouldBeCalled()->willReturn($chatMessage->reveal());

        $this->guessChatMessageLinkableObjectHandler->handle(Argument::type(GuessChatMessageLinkableObject::class))
                ->shouldBeCalled()
                ->willReturn($happening->reveal());

        $this->canDeleteChatMessage->isSatisfiedBy($happening->reveal(), $user->reveal())->shouldBeCalled()->willReturn(true);

        $this->messageRepository->getMessagesCountByLinkableObject($happening->reveal(), null)->willReturn(42);
        $this->notificationPublisher->publishChatMessageNotification(
                $happening->reveal(),
                $chatMessage->reveal(),
                42,
                'delete_chat_message'
            )->shouldBeCalled();
        $this->messageRepository->delete($chatMessage->reveal())->shouldBeCalled();

        $this->deleteChatMessageHandler->handle(new DeleteChatMessage(4242, $user->reveal(), $event->reveal()));
    }

    public function testChatMessageNotFound()
    {
        $this->expectException(ChatMessageNotFoundException::class);
        $event = $this->prophesize(Event::class);
        $user = $this->prophesize(User::class);

        $this->messageRepository->findById(4242)->shouldBeCalled()->willReturn(null);

        $this->messageRepository->delete(Argument::any())->shouldNotBeCalled();

        $this->deleteChatMessageHandler->handle(new DeleteChatMessage(4242, $user->reveal(), $event->reveal()));
    }

    public function testInvalidEvent()
    {
        $this->expectException(ChatMessageNotAllowedException::class);
        $happeningEvent = $this->prophesize(Event::class);
        $happeningEvent->getId()->willReturn(123);
        $happening = $this->prophesize(Happening::class);
        $happening->getEvent()->willReturn($happeningEvent->reveal());
        $user = $this->prophesize(User::class);

        $chatMessage = $this->prophesize(ChatMessage::class);
        $chatMessage->getObjectType()->willReturn(ChatMessage::TYPE_HAPPENING);
        $chatMessage->getObjectId()->willReturn(2002);
        $this->messageRepository->findById(4242)->shouldBeCalled()->willReturn($chatMessage->reveal());

        $this->guessChatMessageLinkableObjectHandler->handle(Argument::type(GuessChatMessageLinkableObject::class))
                ->shouldBeCalled()
                ->willReturn($happening->reveal());

        $this->messageRepository->delete(Argument::any())->shouldNotBeCalled();

        $currentEvent = $this->prophesize(Event::class);
        $currentEvent->getId()->willReturn(456);

        $this->deleteChatMessageHandler->handle(new DeleteChatMessage(4242, $user->reveal(), $currentEvent->reveal()));
    }

    public function testDeleteNotAllowed()
    {
        $this->expectException(DeleteChatMessageNotAllowedException::class);
        $event = $this->prophesize(Event::class);
        $event->getId()->willReturn(123);
        $happening = $this->prophesize(Happening::class);
        $happening->getEvent()->willReturn($event->reveal());
        $user = $this->prophesize(User::class);

        $chatMessage = $this->prophesize(ChatMessage::class);
        $chatMessage->getObjectType()->willReturn(ChatMessage::TYPE_HAPPENING);
        $chatMessage->getObjectId()->willReturn(2002);
        $this->messageRepository->findById(4242)->shouldBeCalled()->willReturn($chatMessage->reveal());

        $this->guessChatMessageLinkableObjectHandler->handle(Argument::type(GuessChatMessageLinkableObject::class))
                ->shouldBeCalled()
                ->willReturn($happening->reveal());

        $this->canDeleteChatMessage->isSatisfiedBy($happening->reveal(), $user->reveal())->shouldBeCalled()->willReturn(false);

        $this->messageRepository->delete(Argument::any())->shouldNotBeCalled();

        $this->deleteChatMessageHandler->handle(new DeleteChatMessage(4242, $user->reveal(), $event->reveal()));
    }
}
