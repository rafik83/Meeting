<?php

namespace Proximum\Vimeet\Infrastructure\Repository;

use Doctrine\ORM\EntityManager;
use Proximum\Vimeet\Application\Query\Chat\View\ChatMessageView;
use Proximum\Vimeet\Domain\Model\ChatMessage;
use Proximum\Vimeet\Domain\Model\ChatMessageLinkableInterface;
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
    public function add(ChatMessage $chatMessage): ChatMessage
    {
        $this->entityManager->persist($chatMessage);
        $this->entityManager->flush($chatMessage);

        return $chatMessage;
    }

    /**
     * @return ChatMessageView[]
     */
    public function list(ChatMessageLinkableInterface $object): array
    {
        return $this->entityManager
            ->createQueryBuilder()
            ->select(
                sprintf(
                    'NEW %s(chatMessage.id, chatMessage.content, chatMessage.createdAt, chatMessage.authorName, chatMessage.sheetTitle)',
                    ChatMessageView::class
                )
            )
            ->from(ChatMessage::class, 'chatMessage')
            ->where('chatMessage.objectType = :objectType AND chatMessage.objectId = :objectId')
            ->setParameters(['objectType' => $object->getObjectType(), 'objectId' => $object->getId()])
            ->orderBy('chatMessage.createdAt', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }
}
