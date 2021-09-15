<?php

namespace Proximum\Vimeet\Domain\Token;

use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\User\EventToken\AgendaConfirmationTokenCreated;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Token\UserEventToken;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\Token\UserEventTokenRepositoryInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class UserEventTokenGenerator
{
    /** @var UserEventTokenRepositoryInterface */
    private $userEventTokenRepository;

    /** @var UniqidGenerator */
    private $uniqidGenerator;

    /** @var \DateTimeInterface */
    private $dateTime;

    /** @var EventDispatcherInterface */
    private $eventDispatcher;

    /**
     * @param UserEventTokenRepositoryInterface $userEventTokenRepository
     * @param UniqidGenerator                   $uniqidGenerator
     * @param EventDispatcherInterface          $eventDispatcher
     * @param \DateTimeInterface                $dateTime
     */
    public function __construct(
        UserEventTokenRepositoryInterface $userEventTokenRepository,
        UniqidGenerator $uniqidGenerator,
        EventDispatcherInterface $eventDispatcher,
        \DateTimeInterface $dateTime
    ) {
        $this->userEventTokenRepository = $userEventTokenRepository;
        $this->uniqidGenerator = $uniqidGenerator;
        $this->eventDispatcher = $eventDispatcher;
        $this->dateTime = $dateTime;
    }

    public function getUserEventTokenForConfirmAgenda(Event $event, User $user, string $type): UserEventToken
    {
        $userEventToken = $this->getPreviousToken($event, $user, $type);

        if (null !== $userEventToken) {
            return $userEventToken;
        }

        $userEventToken = $this->generateNewToken($event, $user, $type);

        $this->dispatchEventOfCreation($userEventToken);

        return $userEventToken;
    }

    public function getUserEventToken(Event $event, User $user, string $type): UserEventToken
    {
        $userEventToken = $this->getPreviousToken($event, $user, $type);

        if (null !== $userEventToken) {
            return $userEventToken;
        }

        return $this->generateNewToken($event, $user, $type);
    }

    private function generateNewToken(Event $event, User $user, string $type): UserEventToken
    {
        $uniqid = $this->uniqidGenerator->generate();
        $token = sha1(sprintf('%s%s%s%s%s', $event->getId(), $user->getId(), $type, $this->dateTime->format('c'), $uniqid));

        $userEventToken = new UserEventToken($event, $user, $type, $token, $this->dateTime);

        $this->userEventTokenRepository->add($userEventToken);

        return $userEventToken;
    }

    private function getPreviousToken(Event $event, User $user, string $type): ?UserEventToken
    {
        return $this->userEventTokenRepository->findByEventAndUserAndType($event, $user, $type);
    }

    /**
     * @param UserEventToken $userEventToken
     */
    private function dispatchEventOfCreation(UserEventToken $userEventToken)
    {
        if ($userEventToken->isAgendaConfirmation()) {
            $this->eventDispatcher->dispatch(
                Events::USER_EVENT_TOKEN_AGENDA_CONFIRMATION_CREATED,
                new AgendaConfirmationTokenCreated($userEventToken->getEvent(), $userEventToken->getUser())
            );
        }
    }
}
