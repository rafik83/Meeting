<?php

namespace Proximum\Vimeet\Infrastructure\Repository\Filter;

use Doctrine\ORM\EntityManager;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Filter\BooleanTemplateFilter;
use Proximum\Vimeet\Domain\Repository\Filter\BooleanTemplateFilterRepositoryInterface;

class BooleanTemplateFilterRepository implements BooleanTemplateFilterRepositoryInterface
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
    public function getByEvent(Event $event)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('booleanTemplateFilter')
            ->from(BooleanTemplateFilter::class, 'booleanTemplateFilter')
            ->where('booleanTemplateFilter.event = :event')
            ->setParameter('event', $event);

        return $queryBuilder->getQuery()->getResult();
    }

    public function getByEventIdAndInformationType(int $eventId, string $informationType): array
    {
        return $this
            ->entityManager
            ->createQueryBuilder()
            ->select('booleanTemplateFilter')
            ->from(BooleanTemplateFilter::class, 'booleanTemplateFilter', 'booleanTemplateFilter.templateKey')
            ->where('booleanTemplateFilter.event = :event')
            ->andWhere('booleanTemplateFilter.informationType = :informationType')
            ->setParameters([
                'event' => $eventId,
                'informationType' => $informationType
            ])
            ->getQuery()
            ->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function deleteForEvent(Event $event)
    {
        $this
            ->entityManager
            ->createQueryBuilder()
            ->delete(BooleanTemplateFilter::class, 'booleanTemplateFilter')
            ->where('booleanTemplateFilter.event = :event')
            ->setParameter('event', $event)
            ->getQuery()
            ->execute()
        ;

        $this->entityManager->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function add(BooleanTemplateFilter $booleanTemplateFilter)
    {
        $this->entityManager->persist($booleanTemplateFilter);
        $this->entityManager->flush($booleanTemplateFilter);
    }
}
