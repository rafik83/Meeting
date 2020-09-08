<?php

namespace Proximum\Vimeet\Tests\Application\Command\Chat;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Proximum\Vimeet\Application\Command\Chat\VoteChatMessage;
use Proximum\Vimeet\Application\Command\Chat\VoteChatMessageHandler;
use Proximum\Vimeet\Application\Components\Chat\CheckAccessToChatMessages;
use Proximum\Vimeet\Application\Exception\Chat\ChatMessageNotAllowedException;
use Proximum\Vimeet\Application\Exception\Chat\ChatMessageNotFoundException;
use Proximum\Vimeet\Application\Query\Chat\GuessChatMessageLinkableObject;
use Proximum\Vimeet\Application\Query\Chat\GuessChatMessageLinkableObjectHandler;
use Proximum\Vimeet\Domain\Model\ChatMessage;
use Proximum\Vimeet\Domain\Model\ChatMessageLinkableInterface;
use Proximum\Vimeet\Domain\Model\ChatMessageVote;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\ChatMessageRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\ChatMessageVoteRepositoryInterface;

class VoteChatMessageHandlerTest extends TestCase
{
    /** @var ObjectProphecy */
    private $chatMessageRepository;

    /** @var ObjectProphecy */
    private $chatMessageVoteRepository;

    /** @var ObjectProphecy */
    private $voteChatMessageHandler;

    /** @var ObjectProphecy */
    private $checkAccessToChatMessages;

    public function setUp()
    {
        $this->chatMessageRepository = $this->prophesize(ChatMessageRepositoryInterface::class);
        $this->chatMessageVoteRepository = $this->prophesize(ChatMessageVoteRepositoryInterface::class);
        $this->checkAccessToChatMessages = $this->prophesize(CheckAccessToChatMessages::class);
        $this->guessChatMessageLinkableObjectHandler = $this->prophesize(GuessChatMessageLinkableObjectHandler::class);

        $this->voteChatMessageHandler = new VoteChatMessageHandler(
            $this->chatMessageRepository->reveal(),
            $this->chatMessageVoteRepository->reveal(),
            $this->checkAccessToChatMessages->reveal(),
            $this->guessChatMessageLinkableObjectHandler->reveal()
        );
    }

    public function test_vote_happening_message()
    {
        $user = $this->prophesize(User::class);

        $voteChatMessage = $this->prophesize(VoteChatMessage::class);
        $voteChatMessage->getChatMessageId()
            ->shouldBeCalled()
            ->willReturn(42);
        $voteChatMessage->getUser()
            ->shouldBeCalled()
            ->willReturn($user->reveal());
        $voteChatMessage->getType()
            ->shouldBeCalled()
            ->willReturn('👏');

        $author = $this->prophesize(User::class);
        $author->getId()->shouldBeCalled()->willReturn(456);
        $chatMessage = $this->prophesize(ChatMessage::class);
        $chatMessage->getCreatedBy()->shouldBeCalled()->willReturn($author->reveal());
        $chatMessage->getObjectType()->willReturn('happening');
        $chatMessage->getObjectId()->willReturn(4224);

        $this->chatMessageRepository->findById(42)->shouldBeCalled()->willReturn($chatMessage->reveal());
        $this->chatMessageVoteRepository
            ->getByChatMessageAndUser($chatMessage->reveal(), $user->reveal(), '👏')
            ->shouldBeCalled()
            ->willReturn(null);

        $chatMessageLinkableObject = $this->prophesize(ChatMessageLinkableInterface::class);
        $this->guessChatMessageLinkableObjectHandler->handle(Argument::type(GuessChatMessageLinkableObject::class))
            ->willReturn($chatMessageLinkableObject->reveal());

        $this->checkAccessToChatMessages->isSatisfiedBy($chatMessageLinkableObject->reveal(), $user->reveal())
            ->shouldBeCalled()
            ->willReturn(true);

        $this->chatMessageVoteRepository->add(Argument::type(ChatMessageVote::class))->shouldBeCalled();

        $this->voteChatMessageHandler->handle($voteChatMessage->reveal());
    }

    public function test_unvote_happening_message()
    {
        $user = $this->prophesize(User::class);

        $voteChatMessage = $this->prophesize(VoteChatMessage::class);
        $voteChatMessage->getChatMessageId()
            ->shouldBeCalled()
            ->willReturn(42);
        $voteChatMessage->getUser()
            ->shouldBeCalled()
            ->willReturn($user->reveal());
        $voteChatMessage->getType()
            ->shouldBeCalled()
            ->willReturn('👏');

        $author = $this->prophesize(User::class);
        $author->getId()->shouldBeCalled()->willReturn(456);
        $chatMessage = $this->prophesize(ChatMessage::class);
        $chatMessage->getCreatedBy()->shouldBeCalled()->willReturn($author->reveal());
        $chatMessage->getObjectType()->willReturn('happening');
        $chatMessage->getObjectId()->willReturn(4224);

        $this->chatMessageRepository->findById(42)->shouldBeCalled()->willReturn($chatMessage->reveal());

        $chatMessageVote = $this->prophesize(ChatMessageVote::class);
        $this->chatMessageRepository->findById(42)->shouldBeCalled()->willReturn($chatMessage->reveal());
        $this->chatMessageVoteRepository
            ->getByChatMessageAndUser($chatMessage->reveal(), $user->reveal(), '👏')
            ->shouldBeCalled()
            ->willReturn($chatMessageVote->reveal());

        $chatMessageLinkableObject = $this->prophesize(ChatMessageLinkableInterface::class);
        $this->guessChatMessageLinkableObjectHandler->handle(Argument::type(GuessChatMessageLinkableObject::class))
            ->willReturn($chatMessageLinkableObject->reveal());

        $this->checkAccessToChatMessages->isSatisfiedBy($chatMessageLinkableObject->reveal(), $user->reveal())
            ->shouldBeCalled()
            ->willReturn(true);

        $this->chatMessageVoteRepository->remove($chatMessageVote->reveal())->shouldBeCalled();
        $this->chatMessageVoteRepository->add(Argument::any())->shouldNotBeCalled();

        $this->voteChatMessageHandler->handle($voteChatMessage->reveal());
    }

    public function test_vote_unexpected_message()
    {
        $this->expectException(ChatMessageNotFoundException::class);

        $voteChatMessage = $this->prophesize(VoteChatMessage::class);
        $voteChatMessage->getChatMessageId()
            ->shouldBeCalled()
            ->willReturn(42);

        $this->chatMessageRepository->findById(42)->shouldBeCalled()->willReturn(null);

        $this->chatMessageVoteRepository->add(Argument::any())->shouldNotBeCalled();

        $this->voteChatMessageHandler->handle($voteChatMessage->reveal());
    }

    public function test_vote_for_message_not_in_channel()
    {
        $this->expectException(ChatMessageNotAllowedException::class);

        $user = $this->prophesize(User::class);

        $voteChatMessage = $this->prophesize(VoteChatMessage::class);
        $voteChatMessage->getChatMessageId()
            ->shouldBeCalled()
            ->willReturn(42);
        $voteChatMessage->getUser()
            ->shouldBeCalled()
            ->willReturn($user->reveal());

        $chatMessage = $this->prophesize(ChatMessage::class);
        $chatMessage->getObjectType()->willReturn('happening');
        $chatMessage->getObjectId()->willReturn(4224);
        $this->chatMessageRepository->findById(42)->shouldBeCalled()->willReturn($chatMessage->reveal());

        $chatMessageLinkableObject = $this->prophesize(ChatMessageLinkableInterface::class);
        $this->guessChatMessageLinkableObjectHandler->handle(Argument::type(GuessChatMessageLinkableObject::class))
            ->willReturn($chatMessageLinkableObject->reveal());

        $this->checkAccessToChatMessages->isSatisfiedBy($chatMessageLinkableObject->reveal(), $user->reveal())
            ->shouldBeCalled()
            ->willReturn(false);

        $this->voteChatMessageHandler->handle($voteChatMessage->reveal());
    }

    public function test_vote_self_message()
    {
        $this->expectException(ChatMessageNotAllowedException::class);

        $user = $this->prophesize(User::class);
        $user->getId()->shouldBeCalled()->willReturn(24);

        $voteChatMessage = $this->prophesize(VoteChatMessage::class);
        $voteChatMessage->getChatMessageId()
            ->shouldBeCalled()
            ->willReturn(42);
        $voteChatMessage->getUser()
            ->shouldBeCalled()
            ->willReturn($user->reveal());

        $author = $this->prophesize(User::class);
        $author->getId()->shouldBeCalled()->willReturn(24);
        $chatMessage = $this->prophesize(ChatMessage::class);
        $chatMessage->getCreatedBy()->shouldBeCalled()->willReturn($author->reveal());
        $chatMessage->getObjectType()->willReturn('happening');
        $chatMessage->getObjectId()->willReturn(4224);

        $chatMessageLinkableObject = $this->prophesize(ChatMessageLinkableInterface::class);
        $this->guessChatMessageLinkableObjectHandler->handle(Argument::type(GuessChatMessageLinkableObject::class))
            ->willReturn($chatMessageLinkableObject->reveal());

        $this->checkAccessToChatMessages->isSatisfiedBy($chatMessageLinkableObject->reveal(), $user->reveal())
            ->shouldBeCalled()
            ->willReturn(true);

        $this->chatMessageRepository->findById(42)->shouldBeCalled()->willReturn($chatMessage->reveal());

        $this->chatMessageVoteRepository->add(Argument::any())->shouldNotBeCalled();

        $this->voteChatMessageHandler->handle($voteChatMessage->reveal());
    }
}
