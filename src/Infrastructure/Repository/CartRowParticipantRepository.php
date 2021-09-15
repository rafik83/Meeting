<?php

namespace Proximum\Vimeet\Infrastructure\Repository;

use Doctrine\ORM\EntityManager;
use Proximum\Vimeet\Domain\Model\CartRowParticipant;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Repository\CartRowParticipantRepositoryInterface;

class CartRowParticipantRepository implements CartRowParticipantRepositoryInterface
{
    /** @var EntityManager */
    private $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * {@inheritdoc}
     */
    public function findByParticipant(Participant $participant): ?CartRowParticipant
    {
        $queryBuilder = $this->entityManager->createQueryBuilder()
            ->select('cartRowParticipant, cartRow, product')
            ->from(CartRowParticipant::class, 'cartRowParticipant')
            ->join('cartRowParticipant.participant', 'participant', 'WITH', 'participant.id = :participant')
            ->join('cartRowParticipant.cartRow', 'cartRow')
            ->join('cartRow.product', 'product')
            ->setParameter('participant', $participant->getId())
            ->setMaxResults(1)
        ;

        return $queryBuilder->getQuery()->getOneOrNullResult();
    }

    /**
     * {@inheritdoc}
     */
    public function findCartRowOnAttributableProductForParticipants(array $participants): array
    {
        $queryBuilder = $this->entityManager->createQueryBuilder()
            ->select('cartRowParticipant')
            ->from(CartRowParticipant::class, 'cartRowParticipant')
            ->join('cartRowParticipant.participant', 'participant', 'WITH', 'participant.id IN (:participants)')
            ->join('cartRowParticipant.cartRow', 'cartRow')
            ->join('cartRow.product', 'product', 'WITH', 'product.attributable = true')
            ->setParameter('participants', $participants)
        ;

        return $queryBuilder->getQuery()->getResult();
    }
}
