<?php

namespace Proximum\Vimeet\Infrastructure\Repository\Admin;

use Doctrine\ORM\EntityManager;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Admin\ActivateAccountToken;
use Proximum\Vimeet\Domain\Repository\Admin\ActivateAccountTokenRepositoryInterface;

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
    public function deleteAllForUser(Admin $admin)
    {
        $this
            ->entityManager
            ->createQueryBuilder()
            ->delete('Entity:Admin\ActivateAccountToken', 'activateAccountToken')
            ->where('activateAccountToken.admin = :admin')
            ->setParameter('admin', $admin)
            ->getQuery()
            ->execute();

        $this->entityManager->flush();
    }
}
