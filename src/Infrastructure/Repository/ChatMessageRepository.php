<?php

namespace Proximum\Vimeet\Infrastructure\Repository;

use DateTimeInterface;
use Doctrine\ORM\EntityManager;
use Proximum\Vimeet\Application\Query\Chat\View\ChatMessageView;
use Proximum\Vimeet\Domain\Model\ChatMessage;
use Proximum\Vimeet\Domain\Model\ChatMessageLinkableInterface;
use Proximum\Vimeet\Domain\Model\ChatMessageVote;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Repository\ChatMessageRepositoryInterface;

class ChatMessageRepository implements ChatMessageRepositoryInterface
{
    /** @var EntityManager */
    private $entityManager;

    const OBJECT_TYPES = [
        'NETWORKING' => 'networking'
    ];

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
        $messages = $this->entityManager
            ->createQueryBuilder()
            ->select(
                sprintf(
                    'NEW %s(chatMessage.id, chatMessage.content, chatMessage.createdAt, createdBy.account.avatar, ' .
                        'createdBy.id, chatMessage.authorName, chatMessage.sheetTitle)',
                    ChatMessageView::class
                )
            )
            ->from(ChatMessage::class, 'chatMessage')
            ->join('chatMessage.createdBy', 'createdBy')
            ->where('chatMessage.objectType = :objectType AND chatMessage.objectId = :objectId')
            ->setParameters(['objectType' => $object->getObjectType(), 'objectId' => $object->getId()])
            ->orderBy('chatMessage.createdAt', 'ASC')
            ->getQuery()
            ->getResult();

        $votes = $this->entityManager
            ->createQueryBuilder()
            ->select('chatMessage.id, vote.type, COUNT(vote.id) as count')
            ->from(ChatMessage::class, 'chatMessage')
            ->join(ChatMessageVote::class, 'vote', 'WITH', 'vote.chatMessage = chatMessage')
            ->where('chatMessage.objectType = :objectType')
            ->andWhere('chatMessage.objectId = :objectId')
            ->setParameters(['objectType' => $object->getObjectType(), 'objectId' => $object->getId()])
            ->addGroupBy('chatMessage.id')
            ->addGroupBy('vote.type')
            ->getQuery()
            ->getArrayResult();

        if (count($votes)) {
            $indexedVotes = array_reduce($votes, function ($carry, $row) {
                $carry[$row['id']][$row['type']] = $row['count'];
                return $carry;
            });
            foreach ($messages as $message) {
                $message->votes = $indexedVotes[$message->id] ?? [];
            }
        }

        return $messages;
    }

    public function findById(int $id): ?ChatMessage
    {
        return $this->entityManager->find(ChatMessage::class, $id);
    }

    public function getMessagesCountByEvent(Event $event, ?DateTimeInterface $viewedAfter): int
    {
        $queryBuilder = $this->entityManager
            ->createQueryBuilder()
            ->select('COUNT(chatMessage.id) as count')
            ->from(ChatMessage::class, 'chatMessage')
            ->where('chatMessage.objectType = :objectType')
            ->andWhere('chatMessage.objectId = :eventId')
            ->setParameters([
                'eventId' => $event->getId(),
                'objectType' => Self::OBJECT_TYPES['NETWORKING'],
            ]);

        if ($viewedAfter) {
            $queryBuilder
                ->andWhere('chatMessage.createdAt > :viewedAfter')
                ->setParameter('viewedAfter', $viewedAfter);
        }

        return $queryBuilder->getQuery()->getSingleScalarResult();
    }
}
