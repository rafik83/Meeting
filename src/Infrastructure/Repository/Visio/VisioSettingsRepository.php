<?php

namespace Proximum\Vimeet\Infrastructure\Repository\Visio;

use Doctrine\ORM\EntityManager;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Visio\VisioSettings;
use Proximum\Vimeet\Domain\Repository\Visio\VisioSettingsRepositoryInterface;

class VisioSettingsRepository implements VisioSettingsRepositoryInterface
{
    /** @var EntityManager */
    private $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function create(VisioSettings $visioSettings): void
    {
        $this->entityManager->persist($visioSettings);
        $this->entityManager->flush($visioSettings);
    }

    public function update(VisioSettings $visioSettings): void
    {
        $this->entityManager->flush($visioSettings);
    }

    public function getByEvent(Event $event): ?VisioSettings
    {
        return $this->entityManager
            ->createQueryBuilder()
            ->select('visio_settings')
            ->from(VisioSettings::class, 'visio_settings')
            ->where('visio_settings.event = :event')
            ->setParameter('event', $event->getId())
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}
