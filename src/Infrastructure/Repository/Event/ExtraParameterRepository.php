<?php

namespace Proximum\Vimeet\Infrastructure\Repository\Event;

use Doctrine\ORM\EntityManager;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Event\ExtraParameter;
use Proximum\Vimeet\Domain\Repository\Event\ExtraParameterRepositoryInterface;

class ExtraParameterRepository implements ExtraParameterRepositoryInterface
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
    public function add(ExtraParameter $extraParameter)
    {
        $this->entityManager->persist($extraParameter);
        $this->entityManager->flush($extraParameter);
    }

    /**
     * {@inheritdoc}
     */
    public function set(ExtraParameter $extraParameter)
    {
        $this->entityManager->flush($extraParameter);
    }

    /**
     * {@inheritdoc}
     */
    public function remove(ExtraParameter $extraParameter)
    {
        $this->entityManager->remove($extraParameter);
        $this->entityManager->flush($extraParameter);
    }

    /**
     * {@inheritdoc}
     */
    public function findByEventAndType(Event $event, string $type): ?ExtraParameter
    {
        $queryBuilder = $this->entityManager
            ->createQueryBuilder()
            ->select('extra_parameter')
            ->from(ExtraParameter::class, 'extra_parameter')
            ->where('extra_parameter.event = :event')
            ->andWhere('extra_parameter.type = :type')
            ->setParameter('event', $event)
            ->setParameter('type', $type)
            ->setMaxResults(1);

        return $queryBuilder->getQuery()->getOneOrNullResult();
    }

    /**
     * {@inheritdoc}
     */
    public function findByEvent(Event $event)
    {
        $queryBuilder = $this->entityManager
            ->createQueryBuilder()
            ->select('extra_parameter')
            ->from(ExtraParameter::class, 'extra_parameter')
            ->where('extra_parameter.event = :event')
            ->setParameter('event', $event)
            ->orderBy('extra_parameter.createdAt', 'DESC')
        ;

        return $queryBuilder->getQuery()->getResult();
    }
}
