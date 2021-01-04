<?php

namespace Proximum\Vimeet\Infrastructure\Repository\Event;

use Doctrine\ORM\EntityManager;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Event\Content;
use Proximum\Vimeet\Domain\Repository\Event\ContentRepositoryInterface;

class ContentRepository implements ContentRepositoryInterface
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * {@inheritdoc}
     */
    public function add(Content $content)
    {
        $this->entityManager->persist($content);
        $this->entityManager->flush($content);
    }

    /**
     * {@inheritdoc}
     */
    public function set(Content $content)
    {
        $this->entityManager->flush($content);
    }

    /**
     * {@inheritdoc}
     */
    public function findByEventAndType(Event $event, $type)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('content')
            ->from(Content::class, 'content')
            ->where('content.event = :event')
            ->setParameter('event', $event)
            ->andWhere('content.type = :type')
            ->setParameter('type', $type)
            ->setMaxResults(1);

        return $queryBuilder->getQuery()->getOneOrNullResult();
    }
}
