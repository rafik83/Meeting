<?php

namespace Proximum\Vimeet\Infrastructure\Repository\Admin;

use Doctrine\ORM\EntityManager;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Admin\ForgottenPasswordToken;
use Proximum\Vimeet\Domain\Repository\Admin\ForgottenPasswordTokenRepositoryInterface;

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
    public function deleteAllForUser(Admin $admin)
    {
        $this
            ->entityManager
            ->createQueryBuilder()
            ->delete('Entity:Admin\ForgottenPasswordToken', 'forgottenPasswordToken')
            ->where('forgottenPasswordToken.admin = :admin')
            ->setParameter('admin', $admin)
            ->getQuery()
            ->execute();

        $this->entityManager->flush();
    }
}
