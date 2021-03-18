<?php

namespace Proximum\Vimeet\Infrastructure\Repository\User;

use Doctrine\ORM\EntityManager;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Model\User\ActivateAccountToken;
use Proximum\Vimeet\Domain\Repository\User\ActivateAccountTokenRepositoryInterface;

class ActivateAccountTokenRepository implements ActivateAccountTokenRepositoryInterface
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
    public function create(ActivateAccountToken $activateAccountToken)
    {
        $this->entityManager->persist($activateAccountToken);
        $this->entityManager->flush($activateAccountToken);
    }

    /**
     * {@inheritdoc}
     */
    public function deleteAllForUser(User $user)
    {
        $this
            ->entityManager
            ->createQueryBuilder()
            ->delete('Entity:User\ActivateAccountToken', 'activateAccountToken')
            ->where('activateAccountToken.user = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->execute();

        $this->entityManager->flush();
    }
}
