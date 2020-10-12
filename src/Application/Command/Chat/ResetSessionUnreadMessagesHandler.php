<?php

namespace Proximum\Vimeet\Application\Command\Chat;

use Proximum\Vimeet\Domain\Repository\ChatSessionRepositoryInterface;

class ResetSessionUnreadMessagesHandler
{
    /** @var ChatSessionRepositoryInterface */
    private $chatSessionRepository;

    public function __construct(ChatSessionRepositoryInterface $chatSessionRepository)
    {
        $this->chatSessionRepository = $chatSessionRepository;
    }

    public function handle(ResetSessionUnreadMessages $command): void
    {
        $command->chatSession->resetUnreadMessages($command->user);
        $this->chatSessionRepository->update();
    }
}
