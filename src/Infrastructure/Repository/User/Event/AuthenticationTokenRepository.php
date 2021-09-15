<?php

namespace Proximum\Vimeet\Infrastructure\Repository\User\Event;

use Doctrine\ORM\EntityManager;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Model\User\Event\AuthenticationToken;
use Proximum\Vimeet\Domain\Repository\User\Event\AuthenticationTokenRepositoryInterface;

class AuthenticationTokenRepository implements AuthenticationTokenRepositoryInterface
{
    /** @var EntityManager */
    private $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function add(AuthenticationToken $authenticationToken): void
    {
        $this->entityManager->persist($authenticationToken);
        $this->entityManager->flush($authenticationToken);
    }

    public function set(AuthenticationToken $authenticationToken): void
    {
        $this->entityManager->flush($authenticationToken);
    }

    public function findByEventAndUser(Event $event, User $user): ?AuthenticationToken
    {
        return $this->entityManager
            ->createQueryBuilder()
            ->select('authentication_token')
            ->from(AuthenticationToken::class, 'authentication_token')
            ->where('authentication_token.event = :event')
            ->andWhere('authentication_token.user = :user')
            ->setParameters([
                'event' => $event,
                'user' => $user,
            ])
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
