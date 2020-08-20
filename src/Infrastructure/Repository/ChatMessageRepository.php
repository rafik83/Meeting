<?php

namespace Proximum\Vimeet\Infrastructure\Repository;

use Doctrine\ORM\EntityManager;
use Proximum\Vimeet\Domain\Model\ChatMessage;
use Proximum\Vimeet\Domain\Repository\ChatMessageRepositoryInterface;

class ChatMessageRepository implements ChatMessageRepositoryInterface
{
    /** @var EntityManager */
    private $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager  = $entityManager;
    }

    /**
     * {@inheritdoc}
     */
    public function add(ChatMessage $chatMessage): void
    {
        $this->entityManager->persist($chatMessage);
        $this->entityManager->flush($chatMessage);
    }
}
