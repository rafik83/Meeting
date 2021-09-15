<?php

namespace Proximum\Vimeet\Infrastructure\Repository\ProductAttributedToParticipant;

use Doctrine\ORM\EntityManager;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Product;
use Proximum\Vimeet\Domain\Model\ProductAttributedToParticipant;
use Proximum\Vimeet\Domain\Repository\ProductAttributedToParticipant\ParticipantWithAttributedProductRepositoryInterface;

class ParticipantWithAttributedProductToRepository implements ParticipantWithAttributedProductRepositoryInterface
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
    public function getParticipantsWithAttributedProductForProduct(array $participants, Product $product): array
    {
        return $this
            ->entityManager
            ->createQueryBuilder()
            ->select('participant')
            ->from(Participant::class, 'participant')
            ->join(
                ProductAttributedToParticipant::class,
                'productAttributedToParticipant',
                'WITH',
                'participant IN (:participants)
                AND productAttributedToParticipant.participant = participant
                AND productAttributedToParticipant.product = :product'
            )
            ->setParameter('product', $product)
            ->setParameter('participants', $participants)
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getParticipantsWithAttributedProduct(array $participants): array
    {
        return $this
            ->entityManager
            ->createQueryBuilder()
            ->select('participant')
            ->from(Participant::class, 'participant')
            ->join(
                ProductAttributedToParticipant::class,
                'productAttributedToParticipant',
                'WITH',
                'participant IN (:participants) AND productAttributedToParticipant.participant = participant'
            )
            ->setParameter('participants', $participants)
            ->getQuery()
            ->getResult()
        ;
    }
}
