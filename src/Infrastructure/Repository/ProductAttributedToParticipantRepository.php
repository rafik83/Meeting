<?php

namespace Proximum\Vimeet\Infrastructure\Repository;

use Doctrine\ORM\EntityManager;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Product;
use Proximum\Vimeet\Domain\Model\ProductAttributedToParticipant;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\ProductAttributedToParticipantRepositoryInterface;

class ProductAttributedToParticipantRepository implements ProductAttributedToParticipantRepositoryInterface
{
    /** @var EntityManager */
    private $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function add(ProductAttributedToParticipant $productAttributedToParticipant): void
    {
        $this->entityManager->persist($productAttributedToParticipant);
        $this->entityManager->flush($productAttributedToParticipant);
    }

    /**
     * {@inheritdoc}
     */
    public function findByProductAndParticipants(Product $product, array $participants): array
    {
        $queryBuilder = $this->entityManager->createQueryBuilder()
            ->select('productAttributedToParticipant')
            ->from(ProductAttributedToParticipant::class, 'productAttributedToParticipant')
            ->where('productAttributedToParticipant.product = :product')
            ->andWhere('productAttributedToParticipant.participant IN (:participants)')
            ->setParameter('product', $product)
            ->setParameter('participants', $participants)
        ;

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function removeBatch(array $productAttributedToParticipants): void
    {
        foreach ($productAttributedToParticipants as $productAttributedToParticipant) {
            $this->entityManager->remove($productAttributedToParticipant);
        }

        $this->entityManager->flush();
    }

    /**
     * @param Participant $participant
     *
     * @return ProductAttributedToParticipant[]
     */
    public function findByParticipant(Participant $participant): array
    {
        $queryBuilder = $this->entityManager->createQueryBuilder()
            ->select('productAttributedToParticipant')
            ->from(ProductAttributedToParticipant::class, 'productAttributedToParticipant')
            ->where('productAttributedToParticipant.participant = :participant')
            ->setParameter('participant', $participant)
        ;

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function findByParticipants(array $participants): array
    {
        $queryBuilder = $this->entityManager->createQueryBuilder()
            ->select('productAttributedToParticipant')
            ->from(ProductAttributedToParticipant::class, 'productAttributedToParticipant')
            ->where('productAttributedToParticipant.participant IN (:participants)')
            ->setParameter('participants', $participants)
        ;

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function participantHasAtLeastOneProduct(Participant $participant, array $products): bool
    {
        return null !== $this->entityManager->createQueryBuilder()
            ->select('productAttributedToParticipant')
            ->from(ProductAttributedToParticipant::class, 'productAttributedToParticipant')
            ->where('productAttributedToParticipant.product IN (:products)')
            ->andWhere('productAttributedToParticipant.participant = :participant')
            ->setParameter('products', $products)
            ->setParameter('participant', $participant)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    public function findProductIdsAttributedByUserAndEvent(User $user, Event $event): array
    {
        $queryBuilder = $this->entityManager->createQueryBuilder()
            ->select('product.id')
            ->from(ProductAttributedToParticipant::class, 'productAttributedToParticipant')
            ->join('productAttributedToParticipant.participant', 'participant', 'WITH', 'participant.user = :user')
            ->join('productAttributedToParticipant.product', 'product', 'WITH', 'product.event = :event')
            ->setParameter('user', $user)
            ->setParameter('event', $event)
            ->groupBy('product')
        ;

        return $queryBuilder->getQuery()->getResult();
    }
}
