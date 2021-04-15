<?php

namespace Proximum\Vimeet\Infrastructure\Repository\Event;

use Doctrine\ORM\EntityManager;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Repository\Event\CustomLinkRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\StaticFormulation\StaticFormulationRepositoryInterface;

class CustomLinkRepository implements CustomLinkRepositoryInterface
{
    private EntityManager $entityManager;
    private StaticFormulationRepositoryInterface $staticFormulationRepository;

    public function __construct(
        EntityManager $entityManager,
        StaticFormulationRepositoryInterface $staticFormulationRepository
    ) {
        $this->entityManager = $entityManager;
        $this->staticFormulationRepository = $staticFormulationRepository;
    }

    public function findByEvent(Event $event): array
    {
        $queryBuilder = $this->entityManager
            ->createQueryBuilder()
            ->select('custom_link')
            ->from(Event\CustomLink::class, 'custom_link')
            ->where('custom_link.event = :event')
            ->setParameter('event', $event)
        ;

        return $queryBuilder->getQuery()->getResult();
    }

    public function add(Event\CustomLink $customLink): void
    {
        $this->entityManager->persist($customLink);
        $this->entityManager->flush($customLink);
    }

    public function remove(Event\CustomLink $customLink): void
    {
        $this->staticFormulationRepository->remove($customLink->getStaticFormulation());
        $this->entityManager->remove($customLink);
        $this->entityManager->flush($customLink);
    }
}
