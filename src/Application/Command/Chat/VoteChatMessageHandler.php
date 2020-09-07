<?php

namespace Proximum\Vimeet\Application\Command\Chat;

use Proximum\Vimeet\Application\Command\Chat\VoteChatMessage;
use Proximum\Vimeet\Application\Components\Chat\CheckAccessToChatMessages;
use Proximum\Vimeet\Application\Exception\Chat\ChatMessageNotAllowedException;
use Proximum\Vimeet\Application\Exception\Chat\ChatMessageNotFoundException;
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

    public function __construct(
        ChatMessageRepositoryInterface $chatMessageRepository,
        ChatMessageVoteRepositoryInterface $chatMessageVoteRepository,
        CheckAccessToChatMessages $checkAccessToChatMessages
    ) {
        $this->chatMessageRepository = $chatMessageRepository;
        $this->chatMessageVoteRepository = $chatMessageVoteRepository;
        $this->checkAccessToChatMessages = $checkAccessToChatMessages;
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

        if (!$this->checkAccessToChatMessages->isSatisfiedBy($command->getchatMessageLinkableObject(), $command->user)) {
            throw new ChatMessageNotAllowedException('Access denied to this chat message');
        }

        if ($command->getUser()->getId() === $chatMessage->getCreatedBy()->getId()) {
            throw new ChatMessageNotAllowedException('Access denied to this chat message');
        }

        $questionVote = $this->questionVoteRepository->getByChatMessageAndUser($chatMessage, $command->getUser(), $command->getType());

        if ($questionVote) {
            $this->questionVoteRepository->remove($questionVote);
            // todo: add notification publish

            return;
        }

        $questionVote = new ChatMessageVote(
            $chatMessage,
            $command->getUser(),
            $command->getType()
        );

        $this->chatMessageVoteRepository->add($questionVote);
        // todo: add notification publish
    }
}
