<?php

namespace Proximum\Vimeet\Application\Command\Chat;

use Proximum\Vimeet\Application\Components\Chat\CheckAccessToChatMessages;
use Proximum\Vimeet\Application\Query\Chat\Exception\AccessDeniedToChatMessages;
use Proximum\Vimeet\Domain\Model\ChatMessage;
use Proximum\Vimeet\Domain\Repository\ChatMessageRepositoryInterface;

class AddChatMessageHandler
{
    /** @var ChatMessageRepositoryInterface */
    private $messageRepository;

    /** @var CheckAccessToChatMessages */
    private $checkAccessToChatMessages;

    /** @var \DateTime */
    private $now;

    public function __construct(
        ChatMessageRepositoryInterface $messageRepository,
        CheckAccessToChatMessages $checkAccessToChatMessages,
        \DateTimeInterface $now
    ) {
        $this->messageRepository = $messageRepository;
        $this->checkAccessToChatMessages = $checkAccessToChatMessages;
        $this->now = $now;
    }

    /**
     * @throws AccessDeniedToChatMessages
     */
    public function handle(AddChatMessage $command): void
    {
        if (!$this->checkAccessToChatMessages->isSatisfiedBy($command->object, $command->user)) {
            throw new AccessDeniedToChatMessages('Access denied to this chat messages');
        }

        $this->messageRepository->add(
            new ChatMessage(
                $command->object,
                $command->user,
                $this->now,
                $command->content,
                $command->user->getFullname(),
                $command->sheet->getTitle()
            )
        );
    }
}
