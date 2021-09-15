<?php

namespace Proximum\Vimeet\Infrastructure\Repository\Order;

use Doctrine\ORM\EntityManager;
use Proximum\Vimeet\Domain\Model\Order\PromotionCode;
use Proximum\Vimeet\Domain\Repository\Order\PromotionCodeRepositoryInterface;

class PromotionCodeRepository implements PromotionCodeRepositoryInterface
{
    /**  @var EntityManager */
    private $entityManager;
    
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function remove(PromotionCode $promotionCode): void
    {
        $this->entityManager->remove($promotionCode);
        $this->entityManager->flush($promotionCode);
    }
    
    /**
     * {@inheritdoc}
     */
    public function findPrices(): array
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('p.id AS product', 'SUM(o.price) AS price')
            ->from(PromotionCode::class, 'o')
            ->innerJoin('o.product', 'p')
            ->groupBy('p.id')
            ->orderBy('p.type, p.name')
        ;
        
        return $queryBuilder->getQuery()->getResult();
    }
}
