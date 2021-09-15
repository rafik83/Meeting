<?php

namespace Proximum\Vimeet\Infrastructure\Repository;

use Doctrine\ORM\EntityManager;
use Proximum\Vimeet\Application\Components\Paginator\Paginator;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Repository\AdminRepositoryInterface;

class AdminRepository implements AdminRepositoryInterface
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var Paginator
     */
    private $paginator;

    /**
     * @param EntityManager $entityManager
     * @param Paginator     $paginator
     */
    public function __construct(EntityManager $entityManager, Paginator $paginator)
    {
        $this->entityManager = $entityManager;
        $this->paginator     = $paginator;
    }

    /**
     * {@inheritdoc}
     */
    public function add(Admin $admin)
    {
        $this->entityManager->persist($admin);
        $this->entityManager->flush($admin);
    }

    public function remove(Admin $admin): void
    {
        $this->entityManager->remove($admin);
        $this->entityManager->flush($admin);
    }

    /**
     * {@inheritdoc}
     */
    public function emailExists($email)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('admin.id')
            ->from('Entity:Admin', 'admin')
            ->where('admin.email = :email')
            ->setParameter('email', $email)
            ->setMaxResults(1);

        return $queryBuilder->getQuery()->getOneOrNullResult() ? true : false;
    }

    /**
     * {@inheritdoc}
     */
    public function set(Admin $admin)
    {
        $this->entityManager->flush($admin);
    }

    /**
     * {@inheritdoc}
     */
    public function findById($id)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('admin')
            ->from(Admin::class, 'admin')
            ->where('admin.id = :id')
            ->setParameter('id', $id)
            ->setMaxResults(1);

        return $queryBuilder->getQuery()->getOneOrNullResult();
    }

    /**
     * {@inheritdoc}
     */
    public function findByEmail(string $email, bool $includeDeleted = false)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('admin')
            ->from('Entity:Admin', 'admin')
            ->where('admin.email = :email')
            ->setParameter('email', $email)
            ->setMaxResults(1);

        if ($includeDeleted === false){
            $queryBuilder->andWhere('admin.deletedAt IS NULL');
        }

        return $queryBuilder->getQuery()->getOneOrNullResult();
    }

    /**
     * {@inheritdoc}
     */
    public function list(array $filters): array
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('admin, event')
            ->from(Admin::class, 'admin', 'admin.id')
            ->leftJoin('admin.events', 'event')
            ->andWhere('admin.deletedAt IS NULL')
            ->orderBy('admin.lastname', 'ASC');

        if (isset($filters['role']) && null !== $filters['role'] && \in_array($filters['role'], Admin::getAllRoles(), true)) {
            $queryBuilder
                ->where('admin.role = :role')
                ->setParameter('role', $filters['role']);
        }

        if (isset($filters['event']) && null !== $filters['event']) {
            $queryBuilder
                ->andWhere($queryBuilder->expr()->orX(
                    'event.id = :eventId',
                    'admin.role = :role_event AND admin.events IS EMPTY'
                ))
                ->setParameter('eventId', $filters['event'])
                ->setParameter('role_event', Admin::ROLE_SUPER_ADMIN);
        }

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function listPaginated($page, $limit, array $filters)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('admin')
            ->from(Admin::class, 'admin', 'admin.id')
            ->andWhere('admin.deletedAt IS NULL')
            ->orderBy('admin.lastname', 'ASC');

        if (isset($filters['role']) && null !== $filters['role'] && in_array($filters['role'], Admin::getAllRoles())) {
            $queryBuilder
                ->where('admin.role = :role')
                ->setParameter('role', $filters['role']);
        }

        if (isset($filters['event']) && null !== $filters['event']) {
            $queryBuilder
                ->leftJoin('admin.events', 'event')
                ->andWhere($queryBuilder->expr()->orX(
                    'event.id = :eventId',
                    'admin.role = :role_event AND admin.events IS EMPTY'
                ))
                ->setParameter('eventId', $filters['event'])
                ->setParameter('role_event', Admin::ROLE_SUPER_ADMIN);
        }

        return $this->paginator->paginate($queryBuilder, $page, $limit, 'admin', 'id');
    }

    /**
     * {@inheritdoc}
     */
    public function all()
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('admin')
            ->from(Admin::class, 'admin')
            ->where('admin.deletedAt IS NULL');

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * Followers are Organizer and Operator which can be assigned to a sheet for commercial follow-up
     *
     * @param Event $event
     *
     * @return array
     */
    public function getFollowers(Event $event)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('admin')
            ->from(Admin::class, 'admin')
            ->join('admin.events', 'event', 'WITH', 'event = :event')
            ->setParameter('event', $event)
            ->where('admin.role IN (:roles)')
            ->andWhere('admin.deletedAt IS NULL')
            ->setParameter('roles', [Admin::ROLE_ORGANIZER, Admin::ROLE_OPERATOR])
            ->orderBy('admin.lastname')
            ->addOrderBy('admin.firstname');

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function getOperatorForOrganizer(Admin $admin, array $filters): array
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('admin, event')
            ->from(Admin::class, 'admin', 'admin.id')
            ->where('admin.role IN (:role)')
            ->andWhere('admin.deletedAt IS NULL')
            ->setParameter('role', [Admin::ROLE_OPERATOR, Admin::ROLE_PARTNER]);

        if (isset($filters['event'])) {
            $queryBuilder
                ->join('admin.events', 'event', 'WITH', 'event = :event')
                ->setParameter('event', $filters['event']);
        } else {
            $queryBuilder
                ->join('admin.events', 'event', 'WITH', 'event in (:events)')
                ->setParameter('events', $admin->getEvents());
        }

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function getAllowedPartner(Event $event, Type $type)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('admin')
            ->where('admin.deletedAt IS NULL')
            ->from(Admin::class, 'admin', 'admin.id')
            ->join('admin.types', 'type', 'WITH', 'admin.role = :role AND type = :type')
            ->setParameter('type', $type)
            ->setParameter('role', Admin::ROLE_PARTNER)
        ;

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function getAllowedOrganizer(Event $event)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('admin')
            ->where('admin.deletedAt IS NULL')
            ->from(Admin::class, 'admin', 'admin.id')
            ->join('admin.events', 'event', 'WITH', 'event = :event AND admin.role = :role')
            ->setParameter('event', $event)
            ->setParameter('role', Admin::ROLE_ORGANIZER);

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function findOneByRole($role)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('admin')
            ->from(Admin::class, 'admin')
            ->where('admin.role = :role')
            ->setParameter('role', $role)
            ->setMaxResults(1);

        return $queryBuilder->getQuery()->getOneOrNullResult();
    }
}
