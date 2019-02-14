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
    
    /**
     * {@inheritdoc}
     */
    public function findPrices(): array
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('orderPromotionCode')
            ->from(PromotionCode::class, 'orderPromotionCode')
            ->groupBy('orderPromotionCode.product')
        ;
        
        return $queryBuilder->getQuery()->getResult();
    }
}
