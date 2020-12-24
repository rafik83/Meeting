<?php

namespace Proximum\Vimeet\Infrastructure\Repository\User;

use Doctrine\ORM\EntityManager;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Model\User\ForgottenPasswordToken;
use Proximum\Vimeet\Domain\Repository\User\ForgottenPasswordTokenRepositoryInterface;

class ForgottenPasswordTokenRepository implements ForgottenPasswordTokenRepositoryInterface
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
    public function create(ForgottenPasswordToken $forgottenPasswordToken)
    {
        $this->entityManager->persist($forgottenPasswordToken);
        $this->entityManager->flush($forgottenPasswordToken);
    }

    /**
     * {@inheritdoc}
     */
    public function deleteAllForUser(User $user)
    {
        $this
            ->entityManager
            ->createQueryBuilder()
            ->delete('Entity:User\ForgottenPasswordToken', 'forgottenPasswordToken')
            ->where('forgottenPasswordToken.user = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->execute();

        $this->entityManager->flush();
    }
}
