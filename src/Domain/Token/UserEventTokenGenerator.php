<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

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

    /**
     * @param Event  $event
     * @param User   $user
     * @param string $type
     *
     * @return UserEventToken
     */
    public function getUserEventTokenForConfirmAgenda(Event $event, User $user, $type)
    {
        $userEventToken = $this->userEventTokenRepository->findByEventAndUserAndType($event, $user, $type);

        if (null !== $userEventToken) {
            return $userEventToken;
        }

        $uniqid = $this->uniqidGenerator->generate();
        $token = sha1(sprintf('%s%s%s%s%s', $event->getId(), $user->getId(), $type, $this->dateTime->format('c'), $uniqid));

        $userEventToken = new UserEventToken($event, $user, $type, $token, $this->dateTime);

        $this->userEventTokenRepository->add($userEventToken);

        $this->dispatchEventOfCreation($userEventToken);

        return $userEventToken;
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
