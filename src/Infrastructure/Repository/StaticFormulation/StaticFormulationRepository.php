<?php

namespace Proximum\Vimeet\Infrastructure\Repository\StaticFormulation;

use Doctrine\ORM\EntityManager;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\StaticFormulation;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Repository\StaticFormulation\StaticFormulationRepositoryInterface;

class StaticFormulationRepository implements StaticFormulationRepositoryInterface
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
    public function add(StaticFormulation $staticFormulation): void
    {
        $this->entityManager->persist($staticFormulation);
        $this->entityManager->flush($staticFormulation);
    }

    /**
     * {@inheritdoc}
     */
    public function set(StaticFormulation $staticFormulation): void
    {
        $this->entityManager->flush($staticFormulation);
    }

    /**
     * {@inheritdoc}
     */
    public function findByEvent(Event $event): array
    {
        return $this->entityManager
            ->createQueryBuilder()
            ->select('static_formulation')
            ->from(StaticFormulation::class, 'static_formulation')
            ->where('static_formulation.event = :event')
            ->setParameter('event', $event)
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function findByEventAndKey(Event $event, string $key): array
    {
        return $this->entityManager
            ->createQueryBuilder()
            ->select('static_formulation')
            ->from(StaticFormulation::class, 'static_formulation')
            ->where('static_formulation.event = :event')
            ->andWhere('static_formulation.staticFormulationKey = :key')
            ->setParameter('event', $event)
            ->setParameter('key', $key)
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function findByTypeAndLocale(Type $type, string $locale): array
    {
        return $this->entityManager
            ->createQueryBuilder()
            ->select('static_formulation, translation')
            ->from(StaticFormulation::class, 'static_formulation')
            ->join('static_formulation.types', 'type', 'WITH', 'type = :type')
            ->join('static_formulation.translations', 'translation', 'WITH', 'translation.locale = :locale')
            ->setParameter('type', $type)
            ->setParameter('locale', $locale)
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function remove(StaticFormulation $staticFormulation): void
    {
        $this->entityManager->remove($staticFormulation);
        $this->entityManager->flush($staticFormulation);
    }
}
