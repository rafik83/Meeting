<?php

namespace Proximum\Vimeet\Infrastructure\Repository\Payment;

use Doctrine\ORM\EntityManager;
use Proximum\Vimeet\Domain\Model\Payment\Payment;
use Proximum\Vimeet\Domain\Repository\Payment\PaymentRepositoryInterface;

class PaymentRepository implements PaymentRepositoryInterface
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
    public function findById($id)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('payment')
            ->from(Payment::class, 'payment')
            ->where('payment = :id')
            ->setParameter('id', $id);

        return $queryBuilder->getQuery()->getOneOrNullResult();
    }
}
