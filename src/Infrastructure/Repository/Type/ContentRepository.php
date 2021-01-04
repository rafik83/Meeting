<?php

namespace Proximum\Vimeet\Infrastructure\Repository\Type;

use Doctrine\ORM\EntityManager;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Model\Type\Content;
use Proximum\Vimeet\Domain\Repository\Type\ContentRepositoryInterface;

class ContentRepository implements ContentRepositoryInterface
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
    public function add(Content $content): void
    {
        $this->entityManager->persist($content);
        $this->entityManager->flush($content);
    }

    /**
     * {@inheritdoc}
     */
    public function update(Content $content): void
    {
        $this->entityManager->flush($content);
    }

    public function remove(Type\Content $content): void
    {
        $this->entityManager->remove($content);
        $this->entityManager->flush($content);
    }

    /**
     * {@inheritdoc}
     */
    public function findByTypeAndAssociatedParticipationType(string $type, Type $associatedParticipationType): ?Content
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('content')
            ->from(Content::class, 'content')
            ->where('content.associatedParticipationType = :associatedParticipationType')
            ->andWhere('content.type = :type')
            ->setParameter('associatedParticipationType', $associatedParticipationType)
            ->setParameter('type', $type)
            ->setMaxResults(1);

        return $queryBuilder->getQuery()->getOneOrNullResult();
    }

    public function hasContentByAssociatedTypes(string $type, array $associatedParticipationTypes): array
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('content.id AS contentId, associatedParticipationType.id AS associatedParticipationTypeId')
            ->from(Content::class, 'content')
            ->join(
                'content.associatedParticipationType',
                'associatedParticipationType',
                'WITH',
                'content.type = :type AND content.associatedParticipationType IN (:associatedParticipationTypes)'
            )
            ->setParameter('associatedParticipationTypes', $associatedParticipationTypes)
            ->setParameter('type', $type)
        ;

        return $queryBuilder->getQuery()->getResult();
    }
}
