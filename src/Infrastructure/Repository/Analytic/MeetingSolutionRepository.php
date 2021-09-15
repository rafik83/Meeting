<?php

namespace Proximum\Vimeet\Infrastructure\Repository\Analytic;

use Doctrine\ORM\EntityManager;
use Proximum\Vimeet\Domain\Model\Analytic\MeetingSolution;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Repository\Analytic\MeetingSolutionRepositoryInterface;

class MeetingSolutionRepository implements MeetingSolutionRepositoryInterface
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
     * @param $meetingSolution
     */
    public function add(MeetingSolution $meetingSolution)
    {
        $this->entityManager->persist($meetingSolution);
        $this->entityManager->flush($meetingSolution);
    }

    /**
     * @param Event $event
     *
     * @return MeetingSolution[]
     */
    public function findByEvent(Event $event): array
    {
        $queryBuilder = $this->entityManager
            ->createQueryBuilder()
            ->select('meetingSolution')
            ->from(MeetingSolution::class, 'meetingSolution')
            ->where('meetingSolution.event = :event')
            ->setParameter('event', $event)
        ;

        return $queryBuilder->getQuery()->getResult();
    }
}
