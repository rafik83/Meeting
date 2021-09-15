<?php

namespace Proximum\Vimeet\Infrastructure\Repository;

use Doctrine\ORM\EntityManager;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\PromotionCodeGroup;
use Proximum\Vimeet\Domain\Repository\PromotionCodeGroupRepositoryInterface;

class PromotionCodeGroupRepository implements PromotionCodeGroupRepositoryInterface
{
    /** @var EntityManager */
    private $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * {@inheritdoc}
     */
    public function add(PromotionCodeGroup $promotionCodeGroup): void
    {
        $this->entityManager->persist($promotionCodeGroup);
        $this->entityManager->flush($promotionCodeGroup);
    }

    public function set(PromotionCodeGroup $promotionCodeGroup): void
    {
        $this->entityManager->flush($promotionCodeGroup);
    }

    /**
     * @param Event $event
     *
     * @return PromotionCodeGroup[]
     */
    public function findByEvent(Event $event): array
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('promotionCodeGroup')
            ->from(PromotionCodeGroup::class, 'promotionCodeGroup')
            ->where('promotionCodeGroup.event = :event')
            ->setParameter('event', $event)
            ->orderBy('promotionCodeGroup.title')
        ;

        return $queryBuilder->getQuery()->getResult();
    }
}
