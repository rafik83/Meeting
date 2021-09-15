<?php

namespace Proximum\Vimeet\Infrastructure\Repository\Invoice;

use Doctrine\ORM\EntityManager;
use Proximum\Vimeet\Domain\Model\Invoice\Prefix;
use Proximum\Vimeet\Domain\Repository\Invoice\PrefixRepositoryInterface;

class PrefixRepository implements PrefixRepositoryInterface
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
    public function add(Prefix $prefix)
    {
        $this->entityManager->persist($prefix);
        $this->entityManager->flush($prefix);
    }

    /**
     * {@inheritdoc}
     */
    public function getAll()
    {
        $queryBuilder = $this->entityManager->createQueryBuilder()
            ->select('prefix')
            ->from(Prefix::class, 'prefix')
            ->orderBy('prefix.title');

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function getDefault()
    {
        $queryBuilder = $this->entityManager->createQueryBuilder()
            ->select('prefix')
            ->from(Prefix::class, 'prefix')
            ->where('prefix.isDefault = true')
            ->setMaxResults(1);

        return $queryBuilder->getQuery()->getOneOrNullResult();
    }
}
