<?php

namespace Proximum\Vimeet\Infrastructure\Repository\Unavailability;

use Doctrine\ORM\EntityManager;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Model\Unavailability\Mass;
use Proximum\Vimeet\Domain\Repository\Unavailability\MassRepositoryInterface;

class MassRepository implements MassRepositoryInterface
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
    public function create(Mass $mass)
    {
        $this->entityManager->persist($mass);
        $this->entityManager->flush($mass);
    }

    /**
     * {@inheritdoc}
     */
    public function update(Mass $mass)
    {
        $this->entityManager->flush($mass);

        foreach ($mass->getTranslations() as $translation) {
            $this->entityManager->flush($translation);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function findByEvent(Event $event, $locale = null)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('mass')
            ->from(Mass::class, 'mass');

        if (null !== $locale) {
            $queryBuilder
                ->join('mass.translations', 'translation', 'WITH', 'translation.locale = :locale')
                ->setParameter('locale', $locale);
        }

        $queryBuilder
            ->where('mass.event = :event')
            ->setParameter('event', $event);

        return $queryBuilder->getQuery()->getResult();
    }

    public function findByType(Type $type, string $locale)
    {
        return $this
            ->entityManager
            ->createQueryBuilder()
            ->select('mass')
            ->from(Mass::class, 'mass')
            ->join('mass.types', 'type', 'WITH', 'type = :type')
            ->join('mass.translations', 'translation', 'WITH', 'translation.locale = :locale')
            ->setParameter('type', $type)
            ->setParameter('locale', $locale)
            ->getQuery()
            ->getResult()
        ;
    }

    public function findByTypes(array $types, string $locale): array
    {
        return $this
            ->entityManager
            ->createQueryBuilder()
            ->select('mass')
            ->from(Mass::class, 'mass')
            ->join('mass.types', 'type', 'WITH', 'type IN (:types)')
            ->join('mass.translations', 'translation', 'WITH', 'translation.locale = :locale')
            ->setParameter('types', $types)
            ->setParameter('locale', $locale)
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function findDispatchByEvent(Event $event)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('mass')
            ->from(Mass::class, 'mass')
            ->andWhere('mass.dispatch = true')
            ->andWhere('mass.event = :event')
            ->setParameter('event', $event);

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function findNotDispatchedByEvent(Event $event)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('mass')
            ->from(Mass::class, 'mass')
            ->andWhere('mass.dispatch = false')
            ->andWhere('mass.event = :event')
            ->setParameter('event', $event);

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function findBlockingByEvent(Event $event)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('mass')
            ->from(Mass::class, 'mass')
            ->where('mass.event = :event')
            ->andWhere('mass.blocking = true')
            ->setParameter('event', $event);

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function remove(Mass $mass)
    {
        $this->entityManager->remove($mass);
        $this->entityManager->flush($mass);
    }
}
