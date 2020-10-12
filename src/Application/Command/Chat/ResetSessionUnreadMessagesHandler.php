<?php

namespace Proximum\Vimeet\Application\Command\Chat;

use Proximum\Vimeet\Application\Adapter\EntityManagerAdapterInterface;

class ResetSessionUnreadMessagesHandler
{
    /** @var EntityManagerAdapterInterface */
    private $entityManager;

    public function __construct(EntityManagerAdapterInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function handle(ResetSessionUnreadMessages $command): void
    {
        $command->chatSession->resetUnreadMessages($command->user);
        $this->entityManager->flush($command->chatSession);
    }
}
