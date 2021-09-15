<?php

namespace Proximum\Vimeet\Infrastructure\Repository;

use Doctrine\ORM\EntityManager;
use Proximum\Vimeet\Domain\Model\ChangeMailToken;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\ChangeMailTokenRepositoryInterface;

class ChangeMailTokenRepository implements ChangeMailTokenRepositoryInterface
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
    public function create(ChangeMailToken $changeMailToken)
    {
        $this->entityManager->persist($changeMailToken);
        $this->entityManager->flush($changeMailToken);
    }

    /**
     * {@inheritdoc}
     */
    public function deleteAllForUser(User $user)
    {
        $this
            ->entityManager
            ->createQueryBuilder()
            ->delete('Entity:ChangeMailToken', 'changeMailToken')
            ->where('changeMailToken.user = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->execute();

        $this->entityManager->flush();
    }
}
