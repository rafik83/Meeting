<?php

namespace Proximum\Vimeet\Infrastructure\Repository\UserEvent;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\UserEvent\UserEventViewRepositoryInterface;

class UserEventViewRepository implements UserEventViewRepositoryInterface
{
    /** @var EntityManager */
    private $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function getByEvent(Event $event): array
    {
        return $this
            ->getCommonQueryBuilder($event)
            ->join('sheet.participants', 'participant')
            ->join('participant.user', 'user')
            ->getQuery()
            ->getResult()
        ;
    }

    public function getAllSheetsByUserAndEvent(User $user, Event $event): array
    {
        return $this
            ->getCommonQueryBuilder($event)
            ->join(
                'sheet.participants',
                'participant',
                'WITH',
                'sheet.owner = :user OR participant.user = :user'
            )
            ->join('participant.user', 'user')
            ->setParameter('event', $event)
            ->setParameter('user', $user)
            ->getQuery()
            ->getResult()
        ;
    }

    private function getCommonQueryBuilder(Event $event): QueryBuilder
    {
        return $this
            ->entityManager
            ->createQueryBuilder()
            ->select('
                sheet.id sheetId,
                owner.id ownerId,
                owner.email ownerEmail,
                owner.account.firstName ownerFirstName,
                owner.account.lastName ownerLastName,
                owner.locale ownerLocale,
                user.id userId,
                user.email userEmail,
                user.account.firstName userFirstName,
                user.account.lastName userLastName,
                user.locale userLocale,
                sheet as sheetObject
            ')
            ->from(Sheet::class, 'sheet')
            ->join('sheet.owner', 'owner', 'WITH', 'sheet.event = :event')
            ->setParameter('event', $event)
        ;
    }
}
