<?php

namespace Proximum\Vimeet\Application\Command\Chat;

use Proximum\Vimeet\Application\Adapter\NotificationPublisherInterface;
use Proximum\Vimeet\Application\Command\Chat\VoteChatMessage;
use Proximum\Vimeet\Application\Components\Chat\CheckAccessToChatMessages;
use Proximum\Vimeet\Application\Exception\Chat\ChatMessageNotAllowedException;
use Proximum\Vimeet\Application\Exception\Chat\ChatMessageNotFoundException;
use Proximum\Vimeet\Application\Query\Chat\GuessChatMessageLinkableObject;
use Proximum\Vimeet\Application\Query\Chat\GuessChatMessageLinkableObjectHandler;
use Proximum\Vimeet\Domain\Model\ChatMessageVote;
use Proximum\Vimeet\Domain\Repository\ChatMessageRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\ChatMessageVoteRepositoryInterface;

class VoteChatMessageHandler
{
    /** @var ChatMessageRepositoryInterface */
    private $chatMessageRepository;

    /** @var ChatMessageVoteRepositoryInterface */
    private $chatMessageVoteRepository;

    /** @var CheckAccessToChatMessages */
    private $checkAccessToChatMessages;

    /** @var GuessChatMessageLinkableObjectHandler */
    private $guessChatMessageLinkableObjectHandler;

    /** @var NotificationPublisherInterface */
    private $notificationPublisher;

    public function __construct(
        ChatMessageRepositoryInterface $chatMessageRepository,
        ChatMessageVoteRepositoryInterface $chatMessageVoteRepository,
        CheckAccessToChatMessages $checkAccessToChatMessages,
        GuessChatMessageLinkableObjectHandler $guessChatMessageLinkableObjectHandler,
        NotificationPublisherInterface $notificationPublisher
    ) {
        $this->chatMessageRepository = $chatMessageRepository;
        $this->chatMessageVoteRepository = $chatMessageVoteRepository;
        $this->checkAccessToChatMessages = $checkAccessToChatMessages;
        $this->guessChatMessageLinkableObjectHandler = $guessChatMessageLinkableObjectHandler;
        $this->notificationPublisher = $notificationPublisher;
    }

    /**
     * @throws ChatMessageNotFoundException
     * @throws ChatMessageNotAllowedException
     */
    public function handle(VoteChatMessage $command): void
    {
        $chatMessage = $this->chatMessageRepository->findById($command->getChatMessageId());
        if (null === $chatMessage) {
            throw new ChatMessageNotFoundException();
        }

        $chatMessageLinkableObject = $this->guessChatMessageLinkableObjectHandler->handle(
            new GuessChatMessageLinkableObject($chatMessage->getObjectType(), $chatMessage->getObjectId())
        );
        if (!$this->checkAccessToChatMessages->isSatisfiedBy($chatMessageLinkableObject, $command->getUser())) {
            throw new ChatMessageNotAllowedException('Access denied to this chat message');
        }

        if ($command->getUser()->getId() === $chatMessage->getCreatedBy()->getId()) {
            throw new ChatMessageNotAllowedException('Access denied to this chat message');
        }

        $chatMessageVote = $this->chatMessageVoteRepository->getByChatMessageAndUser($chatMessage, $command->getUser(), $command->getType());

        if ($chatMessageVote) {
            $this->chatMessageVoteRepository->remove($chatMessageVote);

            $votesCount = $this->chatMessageVoteRepository->getVotesCountByChatMessage($chatMessage);
            $this->notificationPublisher->publishChatVoteNotification($chatMessageLinkableObject, $chatMessage->getId(), $votesCount);

            return;
        }

        $chatMessageVote = new ChatMessageVote(
            $chatMessage,
            $command->getUser(),
            $command->getType()
        );

        // remove previous vote, in case user changes the type of his vote
        $this->chatMessageVoteRepository->removeVotes($chatMessage, $command->getUser());
        $this->chatMessageVoteRepository->add($chatMessageVote);

        $votesCount = $this->chatMessageVoteRepository->getVotesCountByChatMessage($chatMessage);
        $this->notificationPublisher->publishChatVoteNotification($chatMessageLinkableObject, $chatMessage->getId(), $votesCount);
    }
}
