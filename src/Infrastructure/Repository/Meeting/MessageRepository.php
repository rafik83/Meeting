<?php

namespace Proximum\Vimeet\Infrastructure\Repository\Meeting;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query;
use Proximum\Vimeet\Domain\Model\Meeting\Message;
use Proximum\Vimeet\Domain\Model\Meeting\Request;
use Proximum\Vimeet\Domain\Repository\Meeting\MessageRepositoryInterface;

class MessageRepository implements MessageRepositoryInterface
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * MessageRepository constructor.
     *
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * {@inheritdoc}
     */
    public function add(Message $message)
    {
        $this->entityManager->persist($message);
        $this->entityManager->flush($message);
    }

    /**
     * {@inheritdoc}
     */
    public function getLastMessageByRequest(Request $request)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('message.content')
            ->from(Message::class, 'message')
            ->where('message.request = :request')
            ->setParameter('request', $request)
            ->orderBy('message.createdAt', 'DESC')
            ->setMaxResults(1);

        return $queryBuilder->getQuery()->getOneOrNullResult(Query::HYDRATE_SINGLE_SCALAR);
    }

    /**
     * {@inheritdoc}
     */
    public function getMessagesByMeetingRequest(Request $request)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('message')
            ->from(Message::class, 'message')
            ->where('message.request = :request')
            ->setParameter('request', $request)
            ->orderBy('message.createdAt', 'ASC')
        ;

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function getUpdateOrDeleteReasonMessageFromRequestIds(array $requestIds): array
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('message')
            ->from(Message::class, 'message')
            ->join(
                Request::class,
                'request',
                Query\Expr\Join::WITH,
                'request.id in (:requestIds) AND request.updateOrDeleteReasonMessage = message'
            )
            ->setParameter('requestIds', $requestIds)
        ;

        return $queryBuilder->getQuery()->getResult();
    }
}
