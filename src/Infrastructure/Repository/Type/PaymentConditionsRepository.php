<?php

namespace Proximum\Vimeet\Infrastructure\Repository\Type;

use Doctrine\ORM\EntityManager;
use Proximum\Vimeet\Domain\Model\Type\PaymentConditions;
use Proximum\Vimeet\Domain\Repository\Type\PaymentConditionsRepositoryInterface;

class PaymentConditionsRepository implements PaymentConditionsRepositoryInterface
{
    /** @var EntityManager */
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
    public function add(PaymentConditions $paymentConditions)
    {
        $this->entityManager->persist($paymentConditions);
        $this->entityManager->flush($paymentConditions);
    }

    /**
     * {@inheritdoc}
     */
    public function set(PaymentConditions $paymentConditions)
    {
        $this->entityManager->flush($paymentConditions);
    }

    /**
     * {@inheritdoc}
     */
    public function remove(PaymentConditions $paymentConditions)
    {
        $this->entityManager
            ->createQueryBuilder()
            ->delete()
            ->from(PaymentConditions::class, 'paymentConditions')
            ->where('paymentConditions = :paymentConditions')
            ->setParameter('paymentConditions', $paymentConditions)
            ->getQuery()
            ->execute();
    }
}
